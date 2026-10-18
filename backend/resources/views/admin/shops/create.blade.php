@extends('layouts.admin')

@section('title', 'Create New Store')
@section('page-title', 'Create New Store & Owner Account')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-slate-900">Add New Store / Branch</h3>
            <p class="text-sm text-slate-500">Provision a new isolated store and generate owner credentials for the mobile POS app.</p>
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

    <form action="{{ route('admin.shops.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Store Details Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-3 flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <h4 class="font-bold text-base text-slate-800">1. Store Profile & Branding</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Store / Cafe Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600"
                        placeholder="e.g. NesCafe Solapur Branch, Royal Cafe">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Store Contact Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600"
                        placeholder="+91 98220 12345">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Store Official Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600"
                        placeholder="solapur@nescafe.com">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">GSTIN / Tax Number</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600"
                        placeholder="27AAECN1234F1Z8">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Physical Store Address</label>
                <textarea name="address" rows="2"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600"
                    placeholder="e.g. Opposite Old Court, Station Road, Solapur 413001, Maharashtra">{{ old('address') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ old('currency_symbol', '₹') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-blue-700 focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Currency Code</label>
                    <input type="text" name="currency_code" value="{{ old('currency_code', 'INR') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Thermal Paper Size</label>
                    <select name="thermal_paper_size" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600">
                        <option value="mm58" selected>58mm (2-inch standard)</option>
                        <option value="mm80">80mm (3-inch wide)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Receipt Header Note</label>
                    <input type="text" name="receipt_header" value="{{ old('receipt_header', 'Fresh Brews & Bites') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600"
                        placeholder="e.g. Fresh Brews & Bites">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Receipt Footer Thank-You Note</label>
                    <input type="text" name="receipt_footer" value="{{ old('receipt_footer', 'Thank you for visiting! Free Wi-Fi: NesCafe_Guest') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600"
                        placeholder="e.g. Thank you for visiting!">
                </div>
            </div>
        </div>

        <!-- Store Owner Account Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-3 flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <h4 class="font-bold text-base text-slate-800">2. Store Owner Login Credentials</h4>
            </div>
            <p class="text-xs text-slate-500">This user will have full owner access to manage products, categories, sales, and staff for this store only.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Owner Full Name *</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600"
                        placeholder="e.g. Ramesh Patil">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Login Email *</label>
                    <input type="email" name="owner_email" value="{{ old('owner_email') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-600"
                        placeholder="owner@solapurpos.app">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Initial Password *</label>
                    <input type="text" name="owner_password" value="{{ old('owner_password', 'Store@123') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:ring-2 focus:ring-blue-600"
                        placeholder="Minimum 6 characters">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('admin.shops.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md shadow-blue-500/25 transition">
                Create Store & Generate Owner Account
            </button>
        </div>
    </form>
</div>
@endsection
