@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Store Overview')

@section('content')
<div class="space-y-8">
    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Sales Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">This Month Sales</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $currencySymbol }}{{ number_format($monthSales, 2) }}</h3>
                <span class="inline-flex items-center text-xs text-emerald-600 font-medium mt-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                    Gross Revenue
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-primary flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Net Profit Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Net Profit</p>
                <h3 class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-2">{{ $currencySymbol }}{{ number_format($netProfit, 2) }}</h3>
                <span class="text-xs text-gray-500 mt-1 block">After COGS & Expenses</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>

        <!-- Purchases Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Restock Cost</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $currencySymbol }}{{ number_format($monthPurchases, 2) }}</h3>
                <span class="text-xs text-gray-500 mt-1 block">Supplier Purchases</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>

        <!-- Customer Dues Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Customer Dues</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-2">{{ $currencySymbol }}{{ number_format($totalCustomerDues, 2) }}</h3>
                <span class="text-xs text-gray-500 mt-1 block">Receivable Balance</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <!-- Low Stock Alert Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Low Stock Items</p>
                <h3 class="text-2xl font-bold {{ $lowStockProducts->count() > 0 ? 'text-red-600' : 'text-gray-900' }} mt-2">{{ $lowStockProducts->count() }}</h3>
                <span class="text-xs text-gray-500 mt-1 block">Needs Restocking</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Quick Action Banner -->
    <div class="bg-gradient-to-r from-primary to-[#4A00B0] rounded-2xl p-6 text-white flex flex-col md:flex-row items-center justify-between shadow-lg shadow-purple-500/20">
        <div>
            <h3 class="text-lg font-bold">Quick Operations Hub</h3>
            <p class="text-sm text-purple-200 mt-1">Easily restock inventory, import wholesale product sheets, or view sales reports.</p>
        </div>
        <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
            <a href="{{ route('admin.products.create') }}" class="px-4 py-2 bg-white text-primary text-sm font-semibold rounded-xl shadow-sm hover:bg-gray-50 transition">
                + New Product
            </a>
            <a href="{{ route('admin.products.import') }}" class="px-4 py-2 bg-purple-700/60 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl border border-purple-400/30 transition">
                Import Excel Catalog
            </a>
            <a href="{{ route('admin.purchases.create') }}" class="px-4 py-2 bg-purple-700/60 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl border border-purple-400/30 transition">
                + Purchase Restock
            </a>
        </div>
    </div>

    <!-- Low Stock Alert Warning Table (If any) -->
    @if($lowStockProducts->count() > 0)
    <div class="bg-white rounded-2xl border border-red-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-red-50/50 border-b border-red-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <h4 class="font-bold text-sm text-red-900">Critical Low Stock Products</h4>
            </div>
            <a href="{{ route('admin.purchases.create') }}" class="text-xs font-semibold text-red-700 hover:underline">Restock via Supplier PO &rarr;</a>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-6 py-3">Product</th>
                    <th class="px-6 py-3">Barcode/SKU</th>
                    <th class="px-6 py-3">Current Stock</th>
                    <th class="px-6 py-3">Alert Threshold</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($lowStockProducts as $lp)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3.5 font-medium text-gray-900">{{ $lp->name }}</td>
                    <td class="px-6 py-3.5 text-gray-500 font-mono text-xs">{{ $lp->code }}</td>
                    <td class="px-6 py-3.5 text-red-600 font-bold">{{ (int)$lp->stock }} pcs</td>
                    <td class="px-6 py-3.5 text-gray-500">{{ (int)$lp->alert_quantity }} pcs</td>
                    <td class="px-6 py-3.5 text-right">
                        <a href="{{ route('admin.purchases.create') }}" class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 hover:bg-red-200 text-xs font-semibold rounded-lg transition">
                            Restock
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Two-Column Recent Activity (Sales & Purchases) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent POS Sales -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h4 class="font-bold text-base text-gray-900">Recent Sales Invoices</h4>
                <a href="{{ route('admin.reports.sales') }}" class="text-xs text-primary font-semibold hover:underline">View All &rarr;</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentSales as $sale)
                <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                    <div>
                        <p class="font-bold text-sm text-gray-900">{{ $sale->invoice_number }}</p>
                        <p class="text-xs text-gray-500">{{ $sale->customer->name ?? 'Walk-In Customer' }} &bull; {{ $sale->sale_date->format('M d, H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm text-gray-900">{{ $currencySymbol }}{{ number_format($sale->total_amount, 2) }}</p>
                        <span class="inline-block px-2 py-0.5 text-[10px] rounded-full font-semibold {{ $sale->due_amount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $sale->due_amount > 0 ? 'Due: ' . $currencySymbol . number_format($sale->due_amount, 2) : 'Paid in Full' }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400 text-sm">No sales recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Purchases -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h4 class="font-bold text-base text-gray-900">Recent Supplier Restocks</h4>
                <a href="{{ route('admin.purchases.index') }}" class="text-xs text-primary font-semibold hover:underline">View All &rarr;</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentPurchases as $purchase)
                <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                    <div>
                        <p class="font-bold text-sm text-gray-900">{{ $purchase->purchase_number }}</p>
                        <p class="text-xs text-gray-500">{{ $purchase->party->name ?? 'Supplier' }} &bull; {{ $purchase->purchase_date->format('M d, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm text-gray-900">{{ $currencySymbol }}{{ number_format($purchase->total_amount, 2) }}</p>
                        <span class="inline-block px-2 py-0.5 text-[10px] rounded-full font-semibold {{ $purchase->due_amount > 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $purchase->due_amount > 0 ? 'Payable: ' . $currencySymbol . number_format($purchase->due_amount, 2) : 'Settled' }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400 text-sm">No purchases recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
