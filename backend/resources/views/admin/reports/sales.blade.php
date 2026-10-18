@extends('layouts.admin')

@section('title', 'Sales Report')
@section('page-title', 'Sales & Revenue Report')

@section('content')
<div class="space-y-6">
    <!-- Filter Bar & PDF Export -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.reports.sales') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-gray-500 uppercase">From:</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 rounded-xl border border-gray-200 text-sm">
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-gray-500 uppercase">To:</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 rounded-xl border border-gray-200 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition">
                Filter
            </button>
        </form>

        <a href="{{ route('admin.reports.sales', ['start_date' => $startDate, 'end_date' => $endDate, 'pdf' => 1]) }}" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download PDF Report
        </a>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Total Sales Volume</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $currencySymbol }}{{ number_format($totalSales, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">{{ $sales->count() }} Invoices Generated</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Cash/Card Collected</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-2">{{ $currencySymbol }}{{ number_format($totalPaid, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">Realized Revenue</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Outstanding Dues</p>
            <h3 class="text-2xl font-bold text-amber-600 mt-2">{{ $currencySymbol }}{{ number_format($totalDue, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">Pending Customer Dues</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Gross Sales Profit</p>
            <h3 class="text-2xl font-bold text-primary mt-2">{{ $currencySymbol }}{{ number_format($totalProfit, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">Selling Price - Purchase Cost</span>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5">Invoice #</th>
                        <th class="px-6 py-3.5">Date & Time</th>
                        <th class="px-6 py-3.5">Customer</th>
                        <th class="px-6 py-3.5">Cashier</th>
                        <th class="px-6 py-3.5">Total Amount</th>
                        <th class="px-6 py-3.5">Paid</th>
                        <th class="px-6 py-3.5">Due</th>
                        <th class="px-6 py-3.5">Profit</th>
                        <th class="px-6 py-3.5 text-right">Method</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3.5 font-mono font-bold text-gray-900">{{ $s->invoice_number }}</td>
                        <td class="px-6 py-3.5 text-gray-500 text-xs">{{ $s->sale_date->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-3.5 font-medium text-gray-800">{{ $s->customer->name ?? 'Walk-In Customer' }}</td>
                        <td class="px-6 py-3.5 text-gray-500 text-xs">{{ $s->cashier->name ?? 'Cashier' }}</td>
                        <td class="px-6 py-3.5 font-bold text-gray-900">{{ $currencySymbol }}{{ number_format($s->total_amount, 2) }}</td>
                        <td class="px-6 py-3.5 text-emerald-700 font-medium">{{ $currencySymbol }}{{ number_format($s->paid_amount, 2) }}</td>
                        <td class="px-6 py-3.5 font-medium {{ $s->due_amount > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $currencySymbol }}{{ number_format($s->due_amount, 2) }}</td>
                        <td class="px-6 py-3.5 text-primary font-bold">{{ $currencySymbol }}{{ number_format($s->profit_amount, 2) }}</td>
                        <td class="px-6 py-3.5 text-right uppercase text-xs font-semibold text-gray-500">{{ $s->payment_method }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-gray-400">No sales transactions within the selected range.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
