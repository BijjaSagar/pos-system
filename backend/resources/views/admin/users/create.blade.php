@extends('layouts.admin')

@section('title', 'Add Staff Member')
@section('page-title', 'Create New User / Staff Member')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-gray-900">Add Team Member</h3>
            <p class="text-xs text-gray-500 mt-1">Create credentials and configure role permissions for POS and Web Admin.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-xs font-semibold text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Staff List
        </a>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden p-8 space-y-8">
        @csrf

        <!-- 1. Role Selection -->
        <div>
            <label class="block text-xs font-bold uppercase text-gray-500 mb-3">1. Select Staff Role *</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:border-primary border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                    <input type="radio" name="role" value="cashier" checked class="sr-only" onchange="applyRolePresets('cashier')">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-900 text-sm">Cashier</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <p class="text-xs text-gray-500">POS checkout, barcode scanning, thermal printing, customer dues.</p>
                </label>

                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:border-primary border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                    <input type="radio" name="role" value="manager" class="sr-only" onchange="applyRolePresets('manager')">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-900 text-sm">Manager</span>
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    </div>
                    <p class="text-xs text-gray-500">Inventory control, purchase orders, sales reports, staff management.</p>
                </label>

                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:border-primary border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                    <input type="radio" name="role" value="employee" class="sr-only" onchange="applyRolePresets('employee')">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-900 text-sm">Employee</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <p class="text-xs text-gray-500">Warehouse stock receiving, catalog viewing, shelf organization.</p>
                </label>

                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:border-primary border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                    <input type="radio" name="role" value="store_owner" class="sr-only" onchange="applyRolePresets('store_owner')">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-900 text-sm">Store Owner</span>
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    </div>
                    <p class="text-xs text-gray-500">Full administrative ownership, billing, reports, unlimited access.</p>
                </label>
            </div>
            @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- 2. Personal & Login Details -->
        <div>
            <label class="block text-xs font-bold uppercase text-gray-500 mb-3">2. Account Credentials & Contact Information</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Alice Johnson"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address (Login Username) *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. alice@mybusiness.com"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. +1 555-0192"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Login Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- 3. Granular Module Permissions -->
        <div>
            <label class="block text-xs font-bold uppercase text-gray-500 mb-3">3. Granular Access Permissions</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="pos_access" checked class="perm-checkbox mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">POS Terminal Access</span>
                        <span class="text-gray-500">Sell, checkout, scan barcodes & print receipts</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="products_view" checked class="perm-checkbox mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">View Products & Catalog</span>
                        <span class="text-gray-500">Browse inventory stock and product pricing</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="products_manage" class="perm-checkbox mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">Product Management</span>
                        <span class="text-gray-500">Add, edit, delete products & Excel import</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="purchases_manage" class="perm-checkbox mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">Purchases & Restocking</span>
                        <span class="text-gray-500">Create purchase orders & restock stock</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="reports_view" class="perm-checkbox mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">Financial Reports</span>
                        <span class="text-gray-500">View Sales, Purchases, P&L and download PDFs</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="users_manage" class="perm-checkbox mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">Staff & Team Management</span>
                        <span class="text-gray-500">Add, manage, and remove team members</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- 4. Account Status Toggle -->
        <div class="flex items-center space-x-3 pt-2">
            <input type="checkbox" id="is_active" name="is_active" value="1" checked class="rounded text-primary focus:ring-primary">
            <label for="is_active" class="text-xs font-semibold text-gray-700">Account Active (Permit login to POS app and Web Portal immediately)</label>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-purple-700 text-white text-xs font-bold shadow-md transition">
                Create User & Assign Role
            </button>
        </div>
    </form>
</div>

<script>
function applyRolePresets(role) {
    const checkboxes = document.querySelectorAll('.perm-checkbox');
    const presets = {
        'cashier': ['pos_access', 'products_view'],
        'employee': ['products_view'],
        'manager': ['pos_access', 'products_view', 'products_manage', 'purchases_manage', 'reports_view', 'users_manage'],
        'store_owner': ['pos_access', 'products_view', 'products_manage', 'purchases_manage', 'reports_view', 'users_manage']
    };

    const targetList = presets[role] || ['pos_access'];
    checkboxes.forEach(cb => {
        cb.checked = targetList.includes(cb.value);
    });
}
</script>
@endsection
