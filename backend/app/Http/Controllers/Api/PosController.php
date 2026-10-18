<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Ledger;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    /**
     * Get products for POS grid with optional search, category filter, and tier pricing.
     */
    public function getProducts(Request $request)
    {
        $tier = $request->query('tier', 'retailer'); // retailer, dealer, wholesaler
        $query = Product::with(['category', 'brand', 'unit'])->where('is_active', true);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(30);

        // Append active price based on customer tier
        $products->getCollection()->transform(function ($product) use ($tier) {
            $product->active_price = $product->getPriceForTier($tier);
            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Look up a single product by barcode (useful for barcode scanner gun / camera).
     */
    public function getProductByBarcode(Request $request, $code)
    {
        $tier = $request->query('tier', 'retailer');
        $product = Product::with(['category', 'brand', 'unit'])
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found with barcode: ' . $code,
            ], 404);
        }

        $product->active_price = $product->getPriceForTier($tier);

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    /**
     * Get customers list with balance and tier.
     */
    public function getCustomers(Request $request)
    {
        $customers = Party::where('type', 'customer')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'customers' => $customers,
        ]);
    }

    /**
     * Checkout POS Cart -> Generates Sale, Sale Items, updates stock, ledgers, and profit.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'party_id' => 'nullable|exists:parties,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank,due,split',
            'note' => 'nullable|string',
        ]);

        $user = $request->user();
        $shopId = $user->shop_id;

        return DB::transaction(function () use ($request, $user, $shopId) {
            $subtotal = 0.00;
            $totalPurchaseCost = 0.00;
            $itemsToInsert = [];

            // 1. Validate stock and calculate items
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. In stock: {$product->stock}");
                }

                // Decrement inventory stock
                $product->decrement('stock', $item['quantity']);

                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;
                $totalPurchaseCost += ($item['quantity'] * $product->purchase_price);

                $itemsToInsert[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'purchase_price' => $product->purchase_price,
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $discount = (float) ($request->discount_amount ?? 0);
            $tax = (float) ($request->tax_amount ?? 0);
            $grandTotal = round(($subtotal - $discount) + $tax, 2);
            $paid = (float) $request->paid_amount;
            $due = max(0, round($grandTotal - $paid, 2));

            // Gross Profit = Total Selling Subtotal - Total Purchase Cost - Discount
            $profit = round(($subtotal - $totalPurchaseCost) - $discount, 2);

            // Generate clean unique invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            // 2. Create Sale Record
            $sale = Sale::create([
                'shop_id' => $shopId,
                'party_id' => $request->party_id,
                'user_id' => $user->id,
                'invoice_number' => $invoiceNumber,
                'sale_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $grandTotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'profit_amount' => $profit,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'note' => $request->note,
            ]);

            // 3. Attach Items
            foreach ($itemsToInsert as &$row) {
                $row['sale_id'] = $sale->id;
            }
            SaleItem::insert($itemsToInsert);

            // 4. Update Customer Ledger & Outstanding Due Balance
            if ($request->party_id) {
                $customer = Party::find($request->party_id);
                if ($customer) {
                    if ($due > 0) {
                        $customer->increment('current_balance', $due);
                    }
                    Ledger::create([
                        'shop_id' => $shopId,
                        'party_id' => $customer->id,
                        'transaction_type' => 'sale',
                        'reference_no' => $invoiceNumber,
                        'debit' => $grandTotal,
                        'credit' => $paid,
                        'balance' => $customer->current_balance,
                        'description' => "Sale invoice {$invoiceNumber} with {$paid} paid and {$due} due",
                    ]);
                }
            }

            // Load shop info for immediate thermal printer output
            $shop = Shop::find($shopId);

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale' => $sale->load(['items', 'customer', 'cashier']),
                'thermal_receipt_payload' => [
                    'store' => [
                        'name' => $shop->name,
                        'address' => $shop->address,
                        'phone' => $shop->phone,
                        'tax_number' => $shop->tax_number,
                        'currency_symbol' => $shop->currency_symbol,
                        'paper_size' => $shop->thermal_paper_size ?? 'mm58',
                        'header' => $shop->receipt_header,
                        'footer' => $shop->receipt_footer,
                    ],
                    'invoice' => [
                        'invoice_number' => $sale->invoice_number,
                        'date' => $sale->sale_date->format('Y-m-d H:i'),
                        'cashier' => $user->name,
                        'customer' => $sale->customer?->name ?? 'Walk-In Customer',
                        'subtotal' => (float) $sale->subtotal,
                        'discount' => (float) $sale->discount_amount,
                        'tax' => (float) $sale->tax_amount,
                        'grand_total' => (float) $sale->total_amount,
                        'paid_amount' => (float) $sale->paid_amount,
                        'due_amount' => (float) $sale->due_amount,
                        'payment_method' => strtoupper($sale->payment_method),
                        'items' => $sale->items->map(function ($it) {
                            return [
                                'name' => $it->product_name,
                                'quantity' => (float) $it->quantity,
                                'unit_price' => (float) $it->unit_price,
                                'subtotal' => (float) $it->subtotal,
                            ];
                        }),
                    ],
                ],
            ], 201);
        });
    }

    /**
     * Thermal Receipt formatting helper endpoint for reprinting any previous sale.
     */
    public function getInvoiceThermalData(Request $request, $id)
    {
        $sale = Sale::with(['items', 'customer', 'cashier'])->findOrFail($id);
        $shop = Shop::find($sale->shop_id);

        return response()->json([
            'success' => true,
            'store' => [
                'name' => $shop->name,
                'address' => $shop->address,
                'phone' => $shop->phone,
                'tax_number' => $shop->tax_number,
                'currency_symbol' => $shop->currency_symbol,
                'paper_size' => $shop->thermal_paper_size ?? 'mm58',
                'header' => $shop->receipt_header,
                'footer' => $shop->receipt_footer,
            ],
            'invoice' => [
                'invoice_number' => $sale->invoice_number,
                'date' => $sale->sale_date->format('Y-m-d H:i'),
                'cashier' => $sale->cashier?->name ?? 'Cashier',
                'customer' => $sale->customer?->name ?? 'Walk-In Customer',
                'subtotal' => (float) $sale->subtotal,
                'discount' => (float) $sale->discount_amount,
                'tax' => (float) $sale->tax_amount,
                'grand_total' => (float) $sale->total_amount,
                'paid_amount' => (float) $sale->paid_amount,
                'due_amount' => (float) $sale->due_amount,
                'payment_method' => strtoupper($sale->payment_method),
                'items' => $sale->items->map(function ($it) {
                    return [
                        'name' => $it->product_name,
                        'quantity' => (float) $it->quantity,
                        'unit_price' => (float) $it->unit_price,
                        'subtotal' => (float) $it->subtotal,
                    ];
                }),
            ],
        ]);
    }
}
