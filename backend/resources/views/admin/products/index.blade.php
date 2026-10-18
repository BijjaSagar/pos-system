@extends('layouts.admin')

@section('title', 'Products Catalog')
@section('page-title', 'Products Catalog')

@section('content')
<div class="space-y-6">
    <!-- Top Action Toolbar -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search & Filter Form -->
        <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or barcode..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-white">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <select name="category_id" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="stock_status" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Stock Levels</option>
                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock Warning</option>
                <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
            </select>

            @if(request()->anyFilled(['search', 'category_id', 'stock_status']))
                <a href="{{ route('admin.products.index') }}" class="text-xs text-red-600 font-semibold hover:underline">Clear Filters</a>
            @endif
        </form>

        <!-- Right Action Buttons -->
        <div class="flex items-center space-x-3 w-full md:w-auto justify-end">
            <a href="{{ route('admin.products.import') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import Excel/CSV
            </a>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Product
            </a>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Product Name</th>
                        <th class="px-6 py-4">Barcode / SKU</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Cost Price</th>
                        <th class="px-6 py-4">Retail Price</th>
                        <th class="px-6 py-4">Dealer Price</th>
                        <th class="px-6 py-4">Wholesale Price</th>
                        <th class="px-6 py-4">Stock</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-6 py-4 font-semibold text-gray-900">
                            {{ $product->name }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-gray-500">
                            <span class="px-2 py-1 bg-gray-100 rounded-md">{{ $product->code }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-medium">
                            {{ $currencySymbol }}{{ number_format($product->purchase_price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-gray-900 font-bold">
                            {{ $currencySymbol }}{{ number_format($product->retailer_price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-blue-700 font-medium">
                            {{ $currencySymbol }}{{ number_format($product->dealer_price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-purple-700 font-medium">
                            {{ $currencySymbol }}{{ number_format($product->wholesale_price, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($product->stock <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Out of Stock</span>
                            @elseif($product->stock <= $product->alert_quantity)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Low: {{ (int)$product->stock }} {{ $product->unit->short_name ?? 'pcs' }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">{{ (int)$product->stock }} {{ $product->unit->short_name ?? 'pcs' }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="inline-block p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                            No products found. Add products manually or import via Excel.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
