@extends('layouts.admin')

@section('title', 'Profit & Loss Statement')
@section('page-title', 'Profit & Loss Statement')

@section('content')
<div class="space-y-6">
    <!-- Filter Bar & PDF Export -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.reports.profit-loss') }}" method="GET" class="flex flex-wrap items-center gap-3">
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

        <a href="{{ route('admin.reports.profit-loss', ['start_date' => $startDate, 'end_date' => $endDate, 'pdf' => 1]) }}" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download PDF Report
        </a>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Total Revenue</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $currencySymbol }}{{ number_format($totalRevenue, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">Gross Invoiced Sales</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Cost of Goods Sold (COGS)</p>
            <h3 class="text-2xl font-bold text-amber-600 mt-2">{{ $currencySymbol }}{{ number_format($cogs, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">Inventory Acquisition Cost</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Gross Margin</p>
            <h3 class="text-2xl font-bold text-primary mt-2">{{ $currencySymbol }}{{ number_format($grossProfit, 2) }}</h3>
            <span class="text-xs text-gray-500 mt-1 block">Revenue - COGS</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-400">Net Profit</p>
            <h3 class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-2">
                {{ $currencySymbol }}{{ number_format($netProfit, 2) }}
            </h3>
            <span class="text-xs font-medium {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }} mt-1 block">
                Profit Margin: {{ $profitMargin }}%
            </span>
        </div>
    </div>

    <!-- Detailed Financial Breakdown Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h4 class="font-bold text-gray-900 text-sm">Income Statement Breakdown</h4>
            <span class="text-xs text-gray-400 font-medium">Accounting Standards</span>
        </div>
        <div class="p-6">
            <div class="space-y-4 max-w-3xl mx-auto">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="font-semibold text-gray-800">1. Sales Revenue (Invoiced Total)</span>
                    <span class="font-bold text-gray-900">{{ $currencySymbol }}{{ number_format($totalRevenue, 2) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100 pl-4 text-gray-600 text-sm">
                    <span>Less: Cost of Goods Sold (Purchase Cost of Items Sold)</span>
                    <span class="text-red-500">- {{ $currencySymbol }}{{ number_format($cogs, 2) }}</span>
                </div>
                <div class="flex items-center justify-between py-3 border-b-2 border-gray-200 bg-gray-50 px-4 rounded-xl font-bold text-gray-900">
                    <span>Gross Profit</span>
                    <span class="text-primary">{{ $currencySymbol }}{{ number_format($grossProfit, 2) }}</span>
                </div>

                <div class="flex items-center justify-between py-2 border-b border-gray-100 pl-4 text-gray-600 text-sm">
                    <span>Add: Other Operating Income</span>
                    <span class="text-emerald-600">+ {{ $currencySymbol }}{{ number_format($totalIncome, 2) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100 pl-4 text-gray-600 text-sm">
                    <span>Less: Total Operating Expenses (Rent, Utilities, Wages, etc.)</span>
                    <span class="text-red-500">- {{ $currencySymbol }}{{ number_format($totalExpenses, 2) }}</span>
                </div>

                <div class="flex items-center justify-between py-4 border-2 {{ $netProfit >= 0 ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }} px-6 rounded-2xl font-black text-lg">
                    <span class="{{ $netProfit >= 0 ? 'text-emerald-900' : 'text-red-900' }}">Net Profit / (Loss)</span>
                    <span class="{{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ $currencySymbol }}{{ number_format($netProfit, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
