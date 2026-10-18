@extends('layouts.admin')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product: ' . $product->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden p-8">
        <div class="mb-6 pb-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Update Product</h3>
                <p class="text-xs text-gray-500">Modify SKU, prices, and stock allocation.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-800">
                &larr; Back to Catalog
            </a>
        </div>

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Barcode / SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $product->code) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Category</label>
                    <select name="category_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm bg-white">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Brand</label>
                    <select name="brand_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm bg-white">
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Unit</label>
                    <select name="unit_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm bg-white">
                        <option value="">Select Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ old('unit_id', $product->unit_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->short_name }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Pricing Grid -->
            <div class="bg-purple-50/50 p-6 rounded-2xl border border-purple-100 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Multi-Tier Pricing Matrix</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Purchase Cost ({{ $currencySymbol }})</label>
                        <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" required
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Retail Price ({{ $currencySymbol }})</label>
                        <input type="number" step="0.01" name="retailer_price" value="{{ old('retailer_price', $product->retailer_price) }}" required
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm font-bold text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-blue-700 mb-1">Dealer Price ({{ $currencySymbol }})</label>
                        <input type="number" step="0.01" name="dealer_price" value="{{ old('dealer_price', $product->dealer_price) }}"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-purple-700 mb-1">Wholesale Price ({{ $currencySymbol }})</label>
                        <input type="number" step="0.01" name="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price) }}"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Current Stock Qty</label>
                    <input type="number" step="1" name="stock" value="{{ old('stock', (int)$product->stock) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Low Stock Alert</label>
                    <input type="number" step="1" name="alert_quantity" value="{{ old('alert_quantity', (int)$product->alert_quantity) }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Tax Rate (%)</label>
                    <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Description / Notes</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl shadow-md transition">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
