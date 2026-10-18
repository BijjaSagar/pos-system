<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Warehouse;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminPurchaseController extends Controller
{
    private function getShopId(): int
    {
        return Auth::user()->shop_id ?? \App\Models\Shop::first()?->id ?? 1;
    }

    public function index(Request $request)
    {
        $shopId = $this->getShopId();
        $query = Purchase::where('shop_id', $shopId)->with(['party', 'warehouse', 'items']);

        if ($request->filled('supplier_id')) {
            $query->where('party_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchases = $query->orderBy('id', 'desc')->paginate(15);
        $suppliers = Party::where('shop_id', $shopId)->where('type', 'supplier')->get();

        return view('admin.purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $shopId = $this->getShopId();
        $suppliers = Party::where('shop_id', $shopId)->where('type', 'supplier')->get();
        $warehouses = Warehouse::where('shop_id', $shopId)->get();
        $products = Product::where('shop_id', $shopId)->where('is_active', true)->get();

        return view('admin.purchases.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $shopId = $this->getShopId();
        $user = Auth::user();

        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank,due',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0.00;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                $qty = (float) $item['quantity'];
                $cost = (float) $item['unit_price'];
                $rowSubtotal = $qty * $cost;

                $subtotal += $rowSubtotal;

                // Increment product stock
                $product->increment('stock', $qty);
                // Optionally update purchase price to the latest unit cost
                $product->update(['purchase_price' => $cost]);

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $cost,
                    'subtotal' => $rowSubtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $paid = (float) $request->paid_amount;
            $due = max(0, round($subtotal - $paid, 2));
            $purchaseNo = 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $purchase = Purchase::create([
                'shop_id' => $shopId,
                'warehouse_id' => $request->warehouse_id,
                'party_id' => $request->party_id,
                'user_id' => $user->id,
                'purchase_number' => $purchaseNo,
                'purchase_date' => $request->purchase_date,
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'total_amount' => $subtotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_method' => $request->payment_method,
                'status' => 'received',
                'note' => $request->note,
            ]);

            foreach ($itemsData as &$it) {
                $it['purchase_id'] = $purchase->id;
            }
            PurchaseItem::insert($itemsData);

            // Update Supplier Ledger & Outstanding Balance
            $supplier = Party::find($request->party_id);
            if ($supplier) {
                if ($due > 0) {
                    // Supplier balance negative = we owe them
                    $supplier->decrement('current_balance', $due);
                }

                Ledger::create([
                    'shop_id' => $shopId,
                    'party_id' => $supplier->id,
                    'transaction_type' => 'purchase',
                    'reference_no' => $purchaseNo,
                    'debit' => $paid,
                    'credit' => $subtotal,
                    'balance' => $supplier->current_balance,
                    'description' => "Purchase order {$purchaseNo} total: {$subtotal}, paid: {$paid}, due: {$due}",
                ]);
            }

            DB::commit();
            return redirect()->route('admin.purchases.show', $purchase->id)->with('success', 'Purchase order created and inventory restocked.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to process purchase order: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $shopId = Auth::user()->shop_id;
        $purchase = Purchase::where('shop_id', $shopId)->with(['party', 'warehouse', 'items'])->findOrFail($id);

        return view('admin.purchases.show', compact('purchase'));
    }
}
