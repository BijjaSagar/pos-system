@extends('layouts.admin')

@section('title', 'Edit Store')
@section('page-title', 'Edit Store Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-slate-900">Edit '{{ $shop->name }}'</h3>
            <p class="text-sm text-slate-500">Update store profile, receipt configuration, or store operational status.</p>
        </div>
        <a href="{{ route('admin.shops.index') }}" class="text-sm text-slate-600 hover:text-slate-900 flex items-center">
            &larr; Back to Stores
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.shops.update', $shop->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Store / Cafe Name *</label>
                    <input type="text" name="name" value="{{ old('name', $shop->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Store Contact Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $shop->phone) }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Store Official Email</label>
                    <input type="email" name="email" value="{{ old('email', $shop->email) }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">GSTIN / Tax Number</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $shop->tax_number) }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Physical Store Address</label>
                <textarea name="address" rows="2"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">{{ old('address', $shop->address) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $shop->currency_symbol ?? '₹') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-blue-700 focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Currency Code</label>
                    <input type="text" name="currency_code" value="{{ old('currency_code', $shop->currency_code ?? 'INR') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Thermal Paper Size</label>
                    <select name="thermal_paper_size" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                        <option value="mm58" {{ $shop->thermal_paper_size === 'mm58' ? 'selected' : '' }}>58mm (2-inch standard)</option>
                        <option value="mm80" {{ $shop->thermal_paper_size === 'mm80' ? 'selected' : '' }}>80mm (3-inch wide)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Receipt Header Note</label>
                    <input type="text" name="receipt_header" value="{{ old('receipt_header', $shop->receipt_header) }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Receipt Footer Thank-You Note</label>
                    <input type="text" name="receipt_footer" value="{{ old('receipt_footer', $shop->receipt_footer) }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center space-x-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Status</label>
                    <select name="status" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                        <option value="active" {{ $shop->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $shop->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ $shop->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('admin.shops.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md shadow-blue-500/25 transition">
                Save Store Settings
            </button>
        </div>
    </form>
</div>
@endsection
