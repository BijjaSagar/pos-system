@extends('layouts.admin')

@section('title', 'Store & Branding Settings')
@section('page-title', 'Store & Branding Settings')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-gray-900">Store Branding & System Configuration</h3>
            <p class="text-xs text-gray-500 mt-1">Customize your cafe/store name, upload logo, choose brand theme colors, set currency (INR / ₹), and configure thermal receipts.</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                {{ $shop->name }}
            </span>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Settings Forms -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. Store Identity & Contact -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <h4 class="font-bold text-sm text-gray-900 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        1. Store Identity & Contact Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Store / Cafe Name *</label>
                            <input type="text" name="name" id="store_name_input" value="{{ old('name', $shop->name) }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-gray-900 focus:outline-none focus:border-primary"
                                   oninput="updateLivePreview()">
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Contact Phone</label>
                            <input type="text" name="phone" id="store_phone_input" value="{{ old('phone', $shop->phone) }}" placeholder="+91 98220 12345"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary"
                                   oninput="updateLivePreview()">
                            @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Contact Email</label>
                            <input type="email" name="email" value="{{ old('email', $shop->email) }}" placeholder="store@pospro.app"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">GST / VAT / Tax Number</label>
                            <input type="text" name="tax_number" id="store_tax_input" value="{{ old('tax_number', $shop->tax_number) }}" placeholder="e.g. 27ABCDE1234F1Z5"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary"
                                   oninput="updateLivePreview()">
                            @error('tax_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Store Address / City</label>
                            <input type="text" name="address" id="store_address_input" value="{{ old('address', $shop->address) }}" placeholder="Solapur, Maharashtra"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary"
                                   oninput="updateLivePreview()">
                            @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 2. Logo & Brand Colors -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <h4 class="font-bold text-sm text-gray-900 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        2. Logo & Theme Color Customization
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <!-- Logo Upload -->
                        <div class="space-y-3">
                            <label class="block text-xs font-semibold text-gray-700">Store Logo</label>
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 rounded-2xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50 flex-shrink-0" id="logo_preview_box">
                                    @if($shop->logo_url)
                                        <img src="{{ $shop->logo_url }}" id="logo_img_preview" class="w-full h-full object-contain">
                                    @else
                                        <span class="text-xl font-bold text-gray-400" id="logo_letter_preview">{{ substr($shop->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="space-y-1.5 flex-1">
                                    <input type="file" name="logo" id="logo_file_input" accept="image/*" class="text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-100 hover:file:bg-gray-200" onchange="previewUploadedLogo(this)">
                                    <p class="text-[11px] text-gray-400">PNG, JPG, WEBP, SVG up to 2MB</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-1">Or paste Logo Image URL directly:</label>
                                <input type="url" name="logo_url" id="logo_url_input" value="{{ old('logo_url', $shop->logo_url) }}" placeholder="https://example.com/logo.png"
                                       class="w-full px-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-primary"
                                       oninput="previewLogoFromUrl(this.value)">
                            </div>
                        </div>

                        <!-- Brand Accent Color -->
                        <div class="space-y-3">
                            <label class="block text-xs font-semibold text-gray-700">Primary Theme Color *</label>
                            <div class="flex items-center space-x-3">
                                <input type="color" name="primary_color" id="color_picker" value="{{ old('primary_color', $shop->primary_color ?? '#6200EE') }}"
                                       class="w-12 h-12 rounded-xl cursor-pointer border border-gray-200 p-1 bg-white"
                                       onchange="applyThemeColor(this.value)">
                                <input type="text" id="color_hex_text" value="{{ old('primary_color', $shop->primary_color ?? '#6200EE') }}"
                                       class="w-28 px-3 py-2 rounded-xl border border-gray-200 text-xs font-mono font-bold uppercase focus:outline-none"
                                       oninput="applyThemeColor(this.value)">
                            </div>

                            <p class="text-[11px] text-gray-500">Quick Palette Presets:</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="applyThemeColor('#D97706')" class="w-7 h-7 rounded-lg bg-[#D97706] shadow-sm hover:scale-110 transition" title="Coffee Amber (NesCafe)"></button>
                                <button type="button" onclick="applyThemeColor('#5C06E8')" class="w-7 h-7 rounded-lg bg-[#5C06E8] shadow-sm hover:scale-110 transition" title="POS SaaS Purple"></button>
                                <button type="button" onclick="applyThemeColor('#DC2626')" class="w-7 h-7 rounded-lg bg-[#DC2626] shadow-sm hover:scale-110 transition" title="Ruby Red"></button>
                                <button type="button" onclick="applyThemeColor('#059669')" class="w-7 h-7 rounded-lg bg-[#059669] shadow-sm hover:scale-110 transition" title="Emerald Green"></button>
                                <button type="button" onclick="applyThemeColor('#2563EB')" class="w-7 h-7 rounded-lg bg-[#2563EB] shadow-sm hover:scale-110 transition" title="Royal Blue"></button>
                                <button type="button" onclick="applyThemeColor('#0F172A')" class="w-7 h-7 rounded-lg bg-[#0F172A] shadow-sm hover:scale-110 transition" title="Midnight Slate"></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Currency & Pricing (INR / ₹) -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <h4 class="font-bold text-sm text-gray-900 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        3. Currency & Price Display (INR / ₹ Sign)
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Currency Symbol *</label>
                            <input type="text" name="currency_symbol" id="currency_symbol_input" value="{{ old('currency_symbol', $shop->currency_symbol ?? '₹') }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-gray-900 focus:outline-none focus:border-primary"
                                   oninput="updateLivePreview()">
                            <div class="flex gap-1.5 mt-2">
                                <button type="button" onclick="setCurrency('₹', 'INR')" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded text-xs font-bold">₹ (INR)</button>
                                <button type="button" onclick="setCurrency('Rs.', 'INR')" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded text-xs font-bold">Rs.</button>
                                <button type="button" onclick="setCurrency('$', 'USD')" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded text-xs font-bold">$ (USD)</button>
                                <button type="button" onclick="setCurrency('AED', 'AED')" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded text-xs font-bold">AED</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Currency ISO Code *</label>
                            <input type="text" name="currency_code" id="currency_code_input" value="{{ old('currency_code', $shop->currency_code ?? 'INR') }}" required placeholder="e.g. INR"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-gray-900 focus:outline-none focus:border-primary uppercase">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Currency Position *</label>
                            <select name="currency_position" id="currency_pos_select" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold focus:outline-none focus:border-primary" onchange="updateLivePreview()">
                                <option value="prefix" {{ ($shop->currency_position ?? 'prefix') === 'prefix' ? 'selected' : '' }}>Prefix (e.g. ₹ 150.00)</option>
                                <option value="suffix" {{ ($shop->currency_position ?? 'prefix') === 'suffix' ? 'selected' : '' }}>Suffix (e.g. 150.00 ₹)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Price Preview Badge -->
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500 font-medium">Sample Menu Item Price Display:</span>
                        <span class="text-sm font-bold text-emerald-700 bg-emerald-100/60 px-3 py-1 rounded-lg" id="sample_price_badge">
                            {{ $shop->formatPrice(120.00) }}
                        </span>
                    </div>
                </div>

                <!-- 4. Thermal Printer & Receipts -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <h4 class="font-bold text-sm text-gray-900 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        4. Thermal Receipt & Printer Settings
                    </h4>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Thermal Printer Paper Width *</label>
                            <div class="grid grid-cols-2 gap-3 max-w-md">
                                <label class="flex items-center p-3 rounded-xl border-2 cursor-pointer border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                                    <input type="radio" name="thermal_paper_size" value="mm58" {{ ($shop->thermal_paper_size ?? 'mm58') === 'mm58' ? 'checked' : '' }} class="text-primary focus:ring-primary mr-2" onchange="updateLivePreview()">
                                    <div>
                                        <span class="font-bold text-xs text-gray-900 block">58 mm (2-inch)</span>
                                        <span class="text-[11px] text-gray-500">Portable Bluetooth POS Printer</span>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 rounded-xl border-2 cursor-pointer border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                                    <input type="radio" name="thermal_paper_size" value="mm80" {{ ($shop->thermal_paper_size ?? 'mm58') === 'mm80' ? 'checked' : '' }} class="text-primary focus:ring-primary mr-2" onchange="updateLivePreview()">
                                    <div>
                                        <span class="font-bold text-xs text-gray-900 block">80 mm (3-inch)</span>
                                        <span class="text-[11px] text-gray-500">Counter / Kitchen Printer</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Receipt Custom Header (Tagline / Announcements)</label>
                            <textarea name="receipt_header" id="receipt_header_input" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:outline-none focus:border-primary" oninput="updateLivePreview()">{{ old('receipt_header', $shop->receipt_header) }}</textarea>
                            <span class="text-[11px] text-gray-400">Printed below store name on every thermal paper slip.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Receipt Custom Footer (Thank You / Return Policy / Wi-Fi)</label>
                            <textarea name="receipt_footer" id="receipt_footer_input" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-mono focus:outline-none focus:border-primary" oninput="updateLivePreview()">{{ old('receipt_footer', $shop->receipt_footer) }}</textarea>
                            <span class="text-[11px] text-gray-400">Printed at the very bottom of each receipt.</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-8 py-3 bg-primary hover:bg-purple-700 text-white text-sm font-bold rounded-xl shadow-lg transition">
                        Save Store Branding & Settings
                    </button>
                </div>
            </div>

            <!-- Right Col: Real-time Live Thermal Slip Preview -->
            <div class="space-y-4">
                <div class="bg-gray-100 p-4 rounded-2xl border border-gray-200 sticky top-6">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200 mb-4">
                        <span class="text-xs font-bold uppercase text-gray-600 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Live Receipt Preview
                        </span>
                        <span class="text-[10px] bg-white px-2 py-0.5 rounded border border-gray-200 font-mono text-gray-500" id="preview_paper_label">58mm ESC/POS</span>
                    </div>

                    <!-- Simulated Thermal Receipt Slip Paper -->
                    <div class="bg-white p-5 rounded-lg shadow-md font-mono text-[11px] text-gray-800 leading-tight space-y-3 mx-auto max-w-[280px] border-t-4 border-dashed border-gray-300">
                        <div class="text-center space-y-1">
                            <h5 class="font-black text-sm text-gray-900 tracking-wider uppercase" id="preview_store_name">{{ $shop->name }}</h5>
                            <p class="text-[10px] text-gray-600 whitespace-pre-line" id="preview_receipt_header">{{ $shop->receipt_header }}</p>
                            <p class="text-[10px] text-gray-500" id="preview_store_address">{{ $shop->address }}</p>
                            <p class="text-[10px] text-gray-500" id="preview_store_phone">Tel: {{ $shop->phone }}</p>
                            <p class="text-[10px] text-gray-500" id="preview_store_tax">GST: {{ $shop->tax_number ?? '27ABCDE1234F1Z5' }}</p>
                        </div>

                        <div class="border-t border-b border-dashed border-gray-400 py-1 text-[10px] space-y-0.5">
                            <div class="flex justify-between">
                                <span>INV: #2026-0042</span>
                                <span>{{ date('d-m-Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Cashier: Barista</span>
                                <span>Table #4</span>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="space-y-1 text-[10px]">
                            <div class="flex justify-between font-bold border-b border-gray-200 pb-0.5">
                                <span>ITEM</span>
                                <span>QTY</span>
                                <span>AMOUNT</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="truncate max-w-[120px]">Cold Coffee w/ Ice Cream</span>
                                <span>1</span>
                                <span class="preview_currency_display_1">₹110.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="truncate max-w-[120px]">Peri Peri Cheesy Fries</span>
                                <span>1</span>
                                <span class="preview_currency_display_2">₹100.00</span>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="border-t border-dashed border-gray-400 pt-1.5 space-y-1 text-[10px]">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span class="preview_subtotal">₹210.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>GST (5%):</span>
                                <span class="preview_tax">₹10.50</span>
                            </div>
                            <div class="flex justify-between font-bold text-xs border-t border-gray-300 pt-1">
                                <span>TOTAL:</span>
                                <span class="preview_grand_total">₹220.50</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Paid (CASH):</span>
                                <span class="preview_grand_total">₹220.50</span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="text-center pt-2 border-t border-dashed border-gray-300 text-[10px] text-gray-500 whitespace-pre-line" id="preview_receipt_footer">
                            {{ $shop->receipt_footer }}
                        </div>

                        <!-- Barcode Simulation -->
                        <div class="text-center pt-1 text-[9px] text-gray-400">
                            ||| | |||| || | |||| ||| || |<br>
                            INV-20261018-0042
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function applyThemeColor(color) {
    if (!color.startsWith('#')) color = '#' + color;
    document.getElementById('color_picker').value = color;
    document.getElementById('color_hex_text').value = color;
    document.documentElement.style.setProperty('--primary-color', color);
}

function setCurrency(symbol, code) {
    document.getElementById('currency_symbol_input').value = symbol;
    document.getElementById('currency_code_input').value = code;
    updateLivePreview();
}

function updateLivePreview() {
    const name = document.getElementById('store_name_input').value || 'NesCafe Solapur';
    const phone = document.getElementById('store_phone_input').value || '+91 98220 12345';
    const address = document.getElementById('store_address_input').value || 'Solapur, Maharashtra';
    const tax = document.getElementById('store_tax_input').value || '27ABCDE1234F1Z5';
    const header = document.getElementById('receipt_header_input').value || '';
    const footer = document.getElementById('receipt_footer_input').value || '';
    const symbol = document.getElementById('currency_symbol_input').value || '₹';
    const pos = document.getElementById('currency_pos_select').value;

    document.getElementById('preview_store_name').textContent = name;
    document.getElementById('preview_store_phone').textContent = 'Tel: ' + phone;
    document.getElementById('preview_store_address').textContent = address;
    document.getElementById('preview_store_tax').textContent = 'GST: ' + tax;
    document.getElementById('preview_receipt_header').textContent = header;
    document.getElementById('preview_receipt_footer').textContent = footer;

    function formatVal(amount) {
        return pos === 'suffix' ? (amount.toFixed(2) + ' ' + symbol) : (symbol + amount.toFixed(2));
    }

    document.querySelectorAll('.preview_currency_display_1').forEach(el => el.textContent = formatVal(110));
    document.querySelectorAll('.preview_currency_display_2').forEach(el => el.textContent = formatVal(100));
    document.querySelectorAll('.preview_subtotal').forEach(el => el.textContent = formatVal(210));
    document.querySelectorAll('.preview_tax').forEach(el => el.textContent = formatVal(10.5));
    document.querySelectorAll('.preview_grand_total').forEach(el => el.textContent = formatVal(220.5));
    document.getElementById('sample_price_badge').textContent = formatVal(120);
}

function previewUploadedLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const box = document.getElementById('logo_preview_box');
            box.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-contain">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewLogoFromUrl(url) {
    if (url && url.length > 5) {
        const box = document.getElementById('logo_preview_box');
        box.innerHTML = '<img src="' + url + '" class="w-full h-full object-contain" onerror="this.src=\'\'">';
    }
}
</script>
@endsection
