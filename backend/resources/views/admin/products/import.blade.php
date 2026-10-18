@extends('layouts.admin')

@section('title', 'Excel Bulk Import')
@section('page-title', 'Bulk Product Import')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden p-8">
        <div class="flex items-center justify-between pb-6 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Import Product Spreadsheet</h3>
                <p class="text-xs text-gray-500">Upload your product list via CSV or Excel (.xlsx) file.</p>
            </div>
            <a href="{{ route('admin.products.sample-csv') }}" class="inline-flex items-center px-4 py-2 border border-purple-200 bg-purple-50 hover:bg-purple-100 text-primary text-xs font-bold rounded-xl transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Sample CSV
            </a>
        </div>

        <form action="{{ route('admin.products.process-import') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf

            <!-- Upload Area -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Select CSV / Excel File</label>
                <div class="border-2 border-dashed border-gray-200 hover:border-primary rounded-2xl p-8 text-center bg-gray-50/50 transition">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <input type="file" name="file" accept=".csv, .txt, .xlsx" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-dark cursor-pointer">
                    <p class="text-xs text-gray-400 mt-2">Supported formats: .CSV, .XLSX (Max file size: 10MB)</p>
                </div>
            </div>

            <!-- Import Guidelines -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 text-xs text-gray-600 space-y-2">
                <h4 class="font-bold text-gray-800">Spreadsheet Columns Guide:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong class="text-gray-800">name</strong> (Required): The full name of the product.</li>
                    <li><strong class="text-gray-800">code</strong> (Required): Unique Barcode or SKU code. Existing codes will be updated.</li>
                    <li><strong class="text-gray-800">category</strong>: Category name (automatically created if not found).</li>
                    <li><strong class="text-gray-800">purchase_price</strong>: Unit purchase/cost price.</li>
                    <li><strong class="text-gray-800">retailer_price</strong>: Standard selling price at the POS terminal.</li>
                    <li><strong class="text-gray-800">dealer_price & wholesale_price</strong>: Optional wholesale tier pricing.</li>
                    <li><strong class="text-gray-800">stock</strong>: Current quantity in inventory.</li>
                </ul>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl shadow-md transition">
                    Process & Import Catalog
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
