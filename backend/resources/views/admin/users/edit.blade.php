@extends('layouts.admin')

@section('title', 'Edit Staff Member')
@section('page-title', 'Edit User: ' . $user->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-gray-900">Edit Team Member</h3>
            <p class="text-xs text-gray-500 mt-1">Modify credentials, role level, and permissions for {{ $user->name }}.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-xs font-semibold text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Staff List
        </a>
    </div>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden p-8 space-y-8">
        @csrf
        @method('PUT')

        <!-- 1. Role Selection -->
        <div>
            <label class="block text-xs font-bold uppercase text-gray-500 mb-3">1. Assigned Role *</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:border-primary border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                    <input type="radio" name="role" value="cashier" {{ $user->role === 'cashier' ? 'checked' : '' }} class="sr-only">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-900 text-sm">Cashier</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <p class="text-xs text-gray-500">POS checkout, barcode scanning, thermal printing.</p>
                </label>

                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:border-primary border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                    <input type="radio" name="role" value="manager" {{ $user->role === 'manager' ? 'checked' : '' }} class="sr-only">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-900 text-sm">Manager</span>
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    </div>
                    <p class="text-xs text-gray-500">Inventory control, purchase orders, sales reports.</p>
                </label>

                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:border-primary border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                    <input type="radio" name="role" value="employee" {{ $user->role === 'employee' ? 'checked' : '' }} class="sr-only">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-900 text-sm">Employee</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <p class="text-xs text-gray-500">Warehouse stock receiving, catalog viewing.</p>
                </label>

                <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:border-primary border-gray-200 has-[:checked]:border-primary has-[:checked]:bg-purple-50/50">
                    <input type="radio" name="role" value="store_owner" {{ $user->role === 'store_owner' ? 'checked' : '' }} class="sr-only">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-gray-900 text-sm">Store Owner</span>
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    </div>
                    <p class="text-xs text-gray-500">Full ownership, billing, reports, unlimited access.</p>
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
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address (Login Username) *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+1 555-0192"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Reset Password (Leave blank to keep unchanged)</label>
                    <input type="password" name="password" placeholder="New password (optional)"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- 3. Granular Module Permissions -->
        <div>
            <label class="block text-xs font-bold uppercase text-gray-500 mb-3">3. Granular Access Permissions</label>
            @php $currentPerms = $user->permissions ?? []; @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="pos_access" {{ in_array('pos_access', $currentPerms) ? 'checked' : '' }} class="mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">POS Terminal Access</span>
                        <span class="text-gray-500">Sell, checkout, scan barcodes & print receipts</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="products_view" {{ in_array('products_view', $currentPerms) ? 'checked' : '' }} class="mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">View Products & Catalog</span>
                        <span class="text-gray-500">Browse inventory stock and product pricing</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="products_manage" {{ in_array('products_manage', $currentPerms) ? 'checked' : '' }} class="mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">Product Management</span>
                        <span class="text-gray-500">Add, edit, delete products & Excel import</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="purchases_manage" {{ in_array('purchases_manage', $currentPerms) ? 'checked' : '' }} class="mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">Purchases & Restocking</span>
                        <span class="text-gray-500">Create purchase orders & restock stock</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="reports_view" {{ in_array('reports_view', $currentPerms) ? 'checked' : '' }} class="mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">Financial Reports</span>
                        <span class="text-gray-500">View Sales, Purchases, P&L and download PDFs</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="users_manage" {{ in_array('users_manage', $currentPerms) ? 'checked' : '' }} class="mt-0.5 rounded text-primary focus:ring-primary">
                    <div class="ml-2.5 text-xs">
                        <span class="font-bold text-gray-800 block">Staff & Team Management</span>
                        <span class="text-gray-500">Add, manage, and remove team members</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- 4. Account Status Toggle -->
        <div class="flex items-center space-x-3 pt-2">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
            <label for="is_active" class="text-xs font-semibold text-gray-700">Account Active (Permit login to POS app and Web Portal immediately)</label>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary hover:bg-purple-700 text-white text-xs font-bold shadow-md transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
