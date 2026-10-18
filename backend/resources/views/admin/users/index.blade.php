@extends('layouts.admin')

@section('title', 'Staff & User Management')
@section('page-title', 'Staff & User Management')

@section('content')
<div class="space-y-6">
    <!-- Top Action Card & Subscription Plan Quota -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Store Staff & Team Members</h3>
            <p class="text-xs text-gray-500 mt-1">Manage roles, permissions, cashiers, managers, and access credentials for your store.</p>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Quota Badge -->
            <div class="text-right">
                <span class="text-xs font-semibold uppercase text-gray-400 block">Plan Quota</span>
                <span class="text-sm font-bold text-gray-800">{{ $totalUsers }} / {{ $userLimit }} Slots</span>
            </div>

            @if($canAddUser)
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2.5 bg-primary hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Add Staff Member
                </a>
            @else
                <button disabled class="inline-flex items-center px-4 py-2.5 bg-gray-200 text-gray-400 text-xs font-bold rounded-xl cursor-not-allowed" title="User limit reached for current subscription plan">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Plan Limit Reached
                </button>
            @endif
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <div class="relative w-72">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <select name="role" class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-primary">
                    <option value="">All Roles</option>
                    <option value="store_owner" {{ request('role') == 'store_owner' ? 'selected' : '' }}>Store Owner</option>
                    <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                    <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('admin.users.index') }}" class="text-xs text-gray-500 hover:text-gray-900 font-semibold underline">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5">Staff Member</th>
                        <th class="px-6 py-3.5">Role</th>
                        <th class="px-6 py-3.5">Contact Phone</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Permissions</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-purple-100 text-primary flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->role === 'store_owner')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                        Store Owner
                                    </span>
                                @elseif($user->role === 'manager')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                        Manager
                                    </span>
                                @elseif($user->role === 'cashier')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                        Cashier
                                    </span>
                                @elseif($user->role === 'employee')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                        Employee
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-medium text-xs">
                                {{ $user->phone ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                        Suspended
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @if($user->role === 'store_owner')
                                        <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-700 rounded font-semibold">Full Access (All Modules)</span>
                                    @else
                                        @foreach($user->permissions ?? [] as $perm)
                                            <span class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded">
                                                {{ str_replace('_', ' ', $perm) }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="p-1.5 text-gray-400 hover:text-primary transition" title="Edit Staff Member">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    @if($user->id !== Auth::id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this staff member? This will revoke all app and web access.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 transition" title="Delete User">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                No staff members found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
