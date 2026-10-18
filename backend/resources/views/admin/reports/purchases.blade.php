@extends('layouts.admin')

@section('title', 'Purchase Report')
@section('page-title', 'Supplier Purchases Report')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.reports.purchases') }}" method="GET" class="flex flex-wrap items-center gap-3">
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

        <a href="{{ route('admin.reports.purchases', ['start_date' => $startDate, 'end_date' => $endDate, 'pdf' => 1]) }}" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download PDF Report
        </a>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Total Restock Value</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $currencySymbol }}{{ number_format($totalPurchases, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">{{ $purchases->count() }} Purchase Orders</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Paid to Suppliers</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-2">{{ $currencySymbol }}{{ number_format($totalPaid, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">Cash/Bank Disbursed</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Outstanding Accounts Payable</p>
            <h3 class="text-2xl font-bold text-red-600 mt-2">{{ $currencySymbol }}{{ number_format($totalDue, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">Pending Supplier Dues</span>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5">PO Number</th>
                        <th class="px-6 py-3.5">Date</th>
                        <th class="px-6 py-3.5">Supplier</th>
                        <th class="px-6 py-3.5">Total Amount</th>
                        <th class="px-6 py-3.5">Paid</th>
                        <th class="px-6 py-3.5">Due Balance</th>
                        <th class="px-6 py-3.5 text-right">Payment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchases as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3.5 font-mono font-bold text-gray-900">{{ $p->purchase_number }}</td>
                        <td class="px-6 py-3.5 text-gray-500 text-xs">{{ $p->purchase_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-3.5 font-medium text-gray-800">{{ $p->party->name ?? 'Direct Supplier' }}</td>
                        <td class="px-6 py-3.5 font-bold text-gray-900">{{ $currencySymbol }}{{ number_format($p->total_amount, 2) }}</td>
                        <td class="px-6 py-3.5 text-emerald-700 font-medium">{{ $currencySymbol }}{{ number_format($p->paid_amount, 2) }}</td>
                        <td class="px-6 py-3.5 font-medium {{ $p->due_amount > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $currencySymbol }}{{ number_format($p->due_amount, 2) }}</td>
                        <td class="px-6 py-3.5 text-right uppercase text-xs font-semibold text-gray-500">{{ $p->payment_method }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400">No purchase transactions within this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
