@extends('layouts.admin')

@section('title', 'Purchase Order ' . $purchase->purchase_number)
@section('page-title', 'Purchase Order Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('admin.purchases.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-800">
            &larr; Back to Purchases
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl shadow-sm transition">
            Print Purchase Voucher
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8" id="printSection">
        <div class="flex justify-between items-start border-b border-gray-200 pb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ Auth::user()->shop->name ?? 'POS Store' }}</h2>
                <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->shop->address }}</p>
                <p class="text-xs text-gray-500">Phone: {{ Auth::user()->shop->phone }}</p>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-lg uppercase">
                    {{ $purchase->status }}
                </span>
                <p class="font-mono text-base font-bold text-gray-900 mt-2">{{ $purchase->purchase_number }}</p>
                <p class="text-xs text-gray-500">Date: {{ $purchase->purchase_date->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 my-6 text-sm">
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Supplier Info</h4>
                <p class="font-bold text-gray-900">{{ $purchase->party->name ?? 'Direct Supplier' }}</p>
                <p class="text-xs text-gray-500">{{ $purchase->party->phone }}</p>
                <p class="text-xs text-gray-500">{{ $purchase->party->email }}</p>
            </div>
            <div class="text-right">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Receiving Warehouse</h4>
                <p class="font-bold text-gray-900">{{ $purchase->warehouse->name ?? 'Default Outlet' }}</p>
                <p class="text-xs text-gray-500">Payment: {{ strtoupper($purchase->payment_method) }}</p>
            </div>
        </div>

        <table class="w-full text-left text-sm border border-gray-200 rounded-xl overflow-hidden mb-6">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="p-3">Product</th>
                    <th class="p-3 text-center">Quantity</th>
                    <th class="p-3 text-right">Unit Cost</th>
                    <th class="p-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($purchase->items as $it)
                <tr>
                    <td class="p-3 font-medium text-gray-900">{{ $it->product_name }}</td>
                    <td class="p-3 text-center">{{ (int)$it->quantity }}</td>
                    <td class="p-3 text-right">{{ $currencySymbol }}{{ number_format($it->unit_price, 2) }}</td>
                    <td class="p-3 text-right font-bold text-gray-900">{{ $currencySymbol }}{{ number_format($it->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-end">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Grand Total:</span>
                    <span class="font-bold text-gray-900">{{ $currencySymbol }}{{ number_format($purchase->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Paid Amount:</span>
                    <span class="font-semibold text-emerald-700">{{ $currencySymbol }}{{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-100 pt-2">
                    <span class="font-bold text-gray-800">Due Balance:</span>
                    <span class="font-bold {{ $purchase->due_amount > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $currencySymbol }}{{ number_format($purchase->due_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
