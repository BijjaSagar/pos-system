@extends('layouts.admin')

@section('title', 'Supplier Purchases')
@section('page-title', 'Supplier Purchase Orders')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Filter Form -->
        <form action="{{ route('admin.purchases.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <select name="supplier_id" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-gray-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Statuses</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received & Restocked</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </form>

        <a href="{{ route('admin.purchases.create') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Create Purchase Restock
        </a>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">PO Number</th>
                        <th class="px-6 py-4">Supplier</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Warehouse</th>
                        <th class="px-6 py-4">Total Amount</th>
                        <th class="px-6 py-4">Paid</th>
                        <th class="px-6 py-4">Due Balance</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">View Voucher</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchases as $po)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono font-bold text-gray-900">
                            {{ $po->purchase_number }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $po->party->name ?? 'Direct Supplier' }}
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $po->purchase_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $po->warehouse->name ?? 'Default' }}
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900">
                            {{ $currencySymbol }}{{ number_format($po->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-emerald-700 font-medium">
                            {{ $currencySymbol }}{{ number_format($po->paid_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 font-medium {{ $po->due_amount > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            {{ $currencySymbol }}{{ number_format($po->due_amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 capitalize">
                                {{ $po->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.purchases.show', $po->id) }}" class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                                View PO
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                            No purchase orders found. Create a new restock order above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
