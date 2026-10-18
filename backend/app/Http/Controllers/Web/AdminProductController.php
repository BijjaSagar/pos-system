<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminProductController extends Controller
{
    private function getShopId(): int
    {
        return Auth::user()->shop_id ?? \App\Models\Shop::first()?->id ?? 1;
    }

    public function index(Request $request)
    {
        $shopId = $this->getShopId();
        $query = Product::where('shop_id', $shopId)->with(['category', 'brand', 'unit']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('stock', '<=', 'alert_quantity');
            } elseif ($request->stock_status === 'out') {
                $query->where('stock', '<=', 0);
            }
        }

        $products = $query->orderBy('id', 'desc')->paginate(15);
        $categories = Category::where('shop_id', $shopId)->get();
        $brands = Brand::where('shop_id', $shopId)->get();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $shopId = $this->getShopId();
        $categories = Category::where('shop_id', $shopId)->get();
        $brands = Brand::where('shop_id', $shopId)->get();
        $units = Unit::where('shop_id', $shopId)->get();

        return view('admin.products.create', compact('categories', 'brands', 'units'));
    }

    public function store(Request $request)
    {
        $shopId = $this->getShopId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:products,code,NULL,id,shop_id,' . $shopId,
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'retailer_price' => 'required|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['shop_id'] = $shopId;
        $validated['dealer_price'] = $validated['dealer_price'] ?? $validated['retailer_price'];
        $validated['wholesale_price'] = $validated['wholesale_price'] ?? $validated['retailer_price'];
        $validated['alert_quantity'] = $validated['alert_quantity'] ?? 5;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product added successfully.');
    }

    public function edit($id)
    {
        $shopId = $this->getShopId();
        $product = Product::where('shop_id', $shopId)->findOrFail($id);
        $categories = Category::where('shop_id', $shopId)->get();
        $brands = Brand::where('shop_id', $shopId)->get();
        $units = Unit::where('shop_id', $shopId)->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, $id)
    {
        $shopId = $this->getShopId();
        $product = Product::where('shop_id', $shopId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:products,code,' . $id . ',id,shop_id,' . $shopId,
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'retailer_price' => 'required|numeric|min:0',
            'dealer_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $shopId = $this->getShopId();
        $product = Product::where('shop_id', $shopId)->findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function import()
    {
        return view('admin.products.import');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
        ]);

        $shopId = $this->getShopId();
        $file = $request->file('file');
        $path = $file->getRealPath();

        $rows = array_map(fn($line) => str_getcsv($line, ',', '"', "\\"), file($path));
        if (count($rows) <= 1) {
            return back()->withErrors(['file' => 'CSV file is empty or missing data rows.']);
        }

        $header = array_map('trim', array_map('strtolower', $rows[0]));
        $importedCount = 0;

        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (count($row) < 3) continue;

                $data = array_combine($header, array_pad($row, count($header), null));

                $name = trim($data['name'] ?? '');
                $code = trim($data['code'] ?? '');
                if (empty($name) || empty($code)) continue;

                // Auto find or create category
                $categoryId = null;
                if (!empty($data['category'])) {
                    $cat = Category::firstOrCreate(['shop_id' => $shopId, 'name' => trim($data['category'])]);
                    $categoryId = $cat->id;
                }

                // Auto find or create unit
                $unitId = null;
                if (!empty($data['unit'])) {
                    $unit = Unit::firstOrCreate(['shop_id' => $shopId, 'short_name' => trim($data['unit'])], ['name' => trim($data['unit'])]);
                    $unitId = $unit->id;
                }

                Product::updateOrCreate(
                    ['shop_id' => $shopId, 'code' => $code],
                    [
                        'name' => $name,
                        'category_id' => $categoryId,
                        'unit_id' => $unitId,
                        'purchase_price' => (float) ($data['purchase_price'] ?? 0),
                        'retailer_price' => (float) ($data['retailer_price'] ?? 0),
                        'dealer_price' => (float) ($data['dealer_price'] ?? $data['retailer_price'] ?? 0),
                        'wholesale_price' => (float) ($data['wholesale_price'] ?? $data['retailer_price'] ?? 0),
                        'stock' => (float) ($data['stock'] ?? 0),
                        'alert_quantity' => (float) ($data['alert_quantity'] ?? 5),
                        'tax_rate' => (float) ($data['tax_rate'] ?? 0),
                    ]
                );
                $importedCount++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Failed to import products: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.products.index')->with('success', "Successfully imported {$importedCount} products.");
    }

    public function downloadSampleCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_import_sample.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'name', 'code', 'category', 'unit', 'purchase_price',
                'retailer_price', 'dealer_price', 'wholesale_price', 'stock', 'alert_quantity', 'tax_rate'
            ]);
            fputcsv($handle, [
                'Logitech MX Master 3S Mouse', '890104001', 'Peripherals', 'Pcs', '70.00',
                '99.99', '88.00', '82.00', '50', '5', '5.00'
            ]);
            fputcsv($handle, [
                'Samsung 27 Inch 4K Monitor', '890104002', 'Monitors', 'Pcs', '250.00',
                '349.00', '310.00', '290.00', '20', '3', '5.00'
            ]);
            fclose($handle);
        }, 200, $headers);
    }
}
