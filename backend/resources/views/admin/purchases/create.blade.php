@extends('layouts.admin')

@section('title', 'New Purchase Order')
@section('page-title', 'Create Purchase Order (Restock)')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden p-8">
        <div class="mb-6 pb-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Restock Order Details</h3>
                <p class="text-xs text-gray-500">Products received will be automatically added to inventory stock.</p>
            </div>
            <a href="{{ route('admin.purchases.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-800">
                &larr; Back to Purchase List
            </a>
        </div>

        <form action="{{ route('admin.purchases.store') }}" method="POST" id="purchaseForm" class="space-y-6">
            @csrf

            <!-- Header Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Supplier <span class="text-red-500">*</span></label>
                    <select name="party_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm bg-white">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} (Balance: ${{ number_format($s->current_balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Target Warehouse</label>
                    <select name="warehouse_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm bg-white">
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ $w->is_default ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Purchase Date <span class="text-red-500">*</span></label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary text-sm bg-white">
                </div>
            </div>

            <!-- Items Table -->
            <div class="border border-gray-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-sm" id="itemsTable">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3">Select Product</th>
                            <th class="px-4 py-3 w-32">Qty</th>
                            <th class="px-4 py-3 w-36">Unit Cost ($)</th>
                            <th class="px-4 py-3 w-36 text-right">Row Total</th>
                            <th class="px-4 py-3 w-16 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="itemsBody">
                        <tr class="item-row">
                            <td class="p-3">
                                <select name="items[0][product_id]" required class="product-select w-full px-3 py-2 rounded-xl border border-gray-200 text-sm bg-white">
                                    <option value="">Choose Product...</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-cost="{{ $p->purchase_price }}">{{ $p->name }} (SKU: {{ $p->code }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-3">
                                <input type="number" step="1" name="items[0][quantity]" value="1" min="1" required class="qty-input w-full px-3 py-2 rounded-xl border border-gray-200 text-sm">
                            </td>
                            <td class="p-3">
                                <input type="number" step="0.01" name="items[0][unit_price]" value="0.00" min="0" required class="cost-input w-full px-3 py-2 rounded-xl border border-gray-200 text-sm">
                            </td>
                            <td class="p-3 text-right font-bold text-gray-900 row-total">{{ $currencySymbol }}0.00</td>
                            <td class="p-3 text-center">
                                <button type="button" class="text-red-500 hover:text-red-700 remove-row" disabled>&times;</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="p-3 bg-gray-50 border-t border-gray-200">
                    <button type="button" id="addRowBtn" class="text-xs font-bold text-primary hover:text-primary-dark">
                        + Add Another Item
                    </button>
                </div>
            </div>

            <!-- Financials Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Order Notes / Supplier Remarks</label>
                    <textarea name="note" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm" placeholder="Invoice reference number, tracking number, etc..."></textarea>
                </div>

                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 font-medium">Grand Total Cost:</span>
                        <span class="font-bold text-base text-gray-900" id="grandTotalDisplay">{{ $currencySymbol }}0.00</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Paid Amount ({{ $currencySymbol }})</label>
                            <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" value="0.00" min="0" required
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm font-bold text-emerald-700">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm bg-white">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Wire</option>
                                <option value="due">Due / Credit</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.purchases.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl shadow-md transition">
                    Submit & Receive Inventory
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const currencySymbol = '{{ $currencySymbol }}';
    let rowIndex = 1;
    const itemsBody = document.getElementById('itemsBody');
    const addRowBtn = document.getElementById('addRowBtn');

    function calculateTotals() {
        let grand = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const total = qty * cost;
            row.querySelector('.row-total').textContent = currencySymbol + total.toFixed(2);
            grand += total;
        });
        document.getElementById('grandTotalDisplay').textContent = currencySymbol + grand.toFixed(2);
    }

    itemsBody.addEventListener('change', e => {
        if (e.target.classList.contains('product-select')) {
            const opt = e.target.selectedOptions[0];
            const cost = opt.getAttribute('data-cost') || 0;
            const row = e.target.closest('.item-row');
            row.querySelector('.cost-input').value = parseFloat(cost).toFixed(2);
            calculateTotals();
        }
    });

    itemsBody.addEventListener('input', e => {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('cost-input')) {
            calculateTotals();
        }
    });

    addRowBtn.addEventListener('click', () => {
        const firstRow = document.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('.product-select').name = `items[${rowIndex}][product_id]`;
        newRow.querySelector('.qty-input').name = `items[${rowIndex}][quantity]`;
        newRow.querySelector('.cost-input').name = `items[${rowIndex}][unit_price]`;
        newRow.querySelector('.product-select').value = '';
        newRow.querySelector('.qty-input').value = '1';
        newRow.querySelector('.cost-input').value = '0.00';
        newRow.querySelector('.row-total').textContent = currencySymbol + '0.00';
        newRow.querySelector('.remove-row').disabled = false;
        itemsBody.appendChild(newRow);
        rowIndex++;
    });

    itemsBody.addEventListener('click', e => {
        if (e.target.classList.contains('remove-row') && !e.target.disabled) {
            e.target.closest('.item-row').remove();
            calculateTotals();
        }
    });
</script>
@endsection
