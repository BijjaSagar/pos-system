@extends('layouts.admin')

@section('title', 'Stores & Multi-Shop')
@section('page-title', 'Stores & Multi-Shop Management')

@section('content')
<div class="space-y-6">

    @if(session('new_store_credentials'))
        @php $creds = session('new_store_credentials'); @endphp
        <div class="p-6 bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl text-white shadow-xl border border-blue-700/50">
            <div class="flex items-start justify-between">
                <div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 mb-3">
                        ✓ Store & Owner Account Successfully Activated
                    </div>
                    <h3 class="text-xl font-bold">{{ $creds['shop_name'] }} Credentials</h3>
                    <p class="text-sm text-blue-200 mt-1">Share these login details with the store owner. They can immediately sign in to the mobile POS app or web portal:</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-950/60 p-4 rounded-xl border border-blue-500/30">
                <div>
                    <span class="text-xs text-blue-300 uppercase tracking-wider block">Store Name</span>
                    <span class="text-base font-bold text-white">{{ $creds['shop_name'] }}</span>
                </div>
                <div>
                    <span class="text-xs text-blue-300 uppercase tracking-wider block">Login Email</span>
                    <span class="text-base font-mono font-bold text-emerald-400">{{ $creds['email'] }}</span>
                </div>
                <div>
                    <span class="text-xs text-blue-300 uppercase tracking-wider block">Password</span>
                    <span class="text-base font-mono font-bold text-amber-300">{{ $creds['password'] }}</span>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3 items-center text-xs text-blue-200">
                <span>📱 <strong>Mobile App:</strong> Open the single POS App and enter these credentials.</span>
                <span>•</span>
                <span>🌐 <strong>Web Portal:</strong> https://vaakai.app/admin/login</span>
            </div>
        </div>
    @endif

    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">All Registered Stores & Branches</h3>
            <p class="text-sm text-slate-500">Every store has its own independent catalog, stock, sales records, and thermal printer setup.</p>
        </div>
        <a href="{{ route('admin.shops.create') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md shadow-blue-500/20 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create New Store
        </a>
    </div>

    <!-- Stores Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($shops as $s)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col justify-between">
            <div class="p-6">
                <!-- Store Top Row -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center font-bold text-xl shadow-sm">
                            {{ strtoupper(substr($s->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-base text-slate-900">{{ $s->name }}</h4>
                            <p class="text-xs text-slate-500">{{ $s->currency_symbol }} • {{ $s->currency_code }} • {{ $s->thermal_paper_size }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $s->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ ucfirst($s->status) }}
                    </span>
                </div>

                <!-- Info List -->
                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Address:</span>
                        <span class="font-medium text-slate-800 truncate max-w-[180px]">{{ $s->address ?? 'Station Road, Solapur' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Phone:</span>
                        <span class="font-medium text-slate-800">{{ $s->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">GSTIN / Tax:</span>
                        <span class="font-mono text-slate-800">{{ $s->tax_number ?? 'Not Registered' }}</span>
                    </div>
                </div>

                <!-- KPI Metric Pills -->
                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="p-2 bg-slate-50 rounded-xl">
                        <span class="block text-xs text-slate-400">Products</span>
                        <span class="font-bold text-sm text-slate-800">{{ $s->products_count }}</span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-xl">
                        <span class="block text-xs text-slate-400">Sales</span>
                        <span class="font-bold text-sm text-blue-600">{{ $s->sales_count }}</span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-xl">
                        <span class="block text-xs text-slate-400">Staff</span>
                        <span class="font-bold text-sm text-slate-800">{{ $s->users_count }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer Action Row -->
            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Plan: <strong class="text-slate-700">{{ $s->plan->name ?? 'Enterprise' }}</strong></span>
                <a href="{{ route('admin.shops.edit', $s->id) }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                    Edit Store Settings &rarr;
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="pt-4">
        {{ $shops->links() }}
    </div>
</div>
@endsection
