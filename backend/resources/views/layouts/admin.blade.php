<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Store Admin') - POS SAAS</title>
    <!-- Tailwind CSS CDN for instant modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    @php
        $activeShop = Auth::user()->shop ?? \App\Models\Shop::first();
        $primaryColor = $activeShop->primary_color ?? '#6200EE';
    @endphp
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ $primaryColor }}',
                        'primary-dark': '{{ $primaryColor }}',
                        'primary-light': '{{ $primaryColor }}',
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        :root { --primary-color: {{ $primaryColor }}; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex-shrink-0 flex flex-col justify-between">
            <div>
                <!-- Brand & Store Header -->
                <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
                    @if(!empty($activeShop->logo_url))
                        <img src="{{ $activeShop->logo_url }}" alt="Logo" class="w-10 h-10 rounded-xl object-contain bg-white p-1 shadow-md flex-shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center font-bold text-xl text-white shadow-lg flex-shrink-0">
                            {{ strtoupper(substr($activeShop->name ?? 'P', 0, 1)) }}
                        </div>
                    @endif
                    <div class="overflow-hidden">
                        <h1 class="font-bold text-base leading-tight tracking-tight text-white truncate max-w-[145px]" title="{{ $activeShop->name ?? 'POS SAAS' }}">{{ $activeShop->name ?? 'POS SAAS' }}</h1>
                        <p class="text-[11px] text-amber-400 font-semibold truncate max-w-[145px]">{{ $activeShop->currency_symbol ?? '₹' }} • {{ $activeShop->address ? Str::limit($activeShop->address, 16) : 'POS Store' }}</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1 text-sm font-medium">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>

                    <div class="pt-4 pb-1 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Inventory & Catalog</div>

                    <a href="{{ route('admin.products.index') }}" class="flex items-center px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.products.index', 'admin.products.create', 'admin.products.edit') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Products Catalog
                    </a>

                    <a href="{{ route('admin.products.import') }}" class="flex items-center px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.products.import') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Excel Bulk Import
                    </a>

                    <div class="pt-4 pb-1 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Procurement</div>

                    <a href="{{ route('admin.purchases.index') }}" class="flex items-center px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.purchases.*') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Supplier Purchases
                    </a>

                    <div class="pt-4 pb-1 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reports & Analytics</div>

                    <a href="{{ route('admin.reports.sales') }}" class="flex items-center px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reports.sales') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Sales Report
                    </a>

                    <a href="{{ route('admin.reports.purchases') }}" class="flex items-center px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reports.purchases') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Purchase Report
                    </a>

                    <a href="{{ route('admin.reports.profit-loss') }}" class="flex items-center px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reports.profit-loss') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Profit & Loss
                    </a>

                    <div class="pt-4 pb-1 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">User & Roles</div>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Staff & Users
                    </a>

                    <div class="pt-4 pb-1 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Configuration</div>

                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-2.5 rounded-xl transition {{ request()->routeIs('admin.settings.*') ? 'bg-primary text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Settings & Branding
                    </a>
                </nav>
            </div>

            <!-- Footer User Profile -->
            <div class="p-4 border-t border-slate-800 bg-slate-950">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full bg-purple-600/30 text-purple-300 flex items-center justify-center font-bold text-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-semibold text-white truncate max-w-[110px]">{{ Auth::user()->name }}</p>
                            <span class="inline-block px-1.5 py-0.5 text-[10px] rounded bg-purple-900/60 text-purple-300 font-medium">{{ strtoupper(str_replace('_', ' ', Auth::user()->role)) }}</span>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-red-400 p-1.5 rounded-lg transition" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            <!-- Topbar -->
            <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-8">
                <div class="flex items-center space-x-3">
                    <h2 class="font-bold text-xl text-gray-800">@yield('page-title', 'Dashboard')</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                        Plan: {{ Auth::user()->shop->plan->name ?? 'Active' }}
                    </span>
                    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Product
                    </a>
                </div>
            </header>

            <!-- Flash Notifications -->
            <main class="p-8">
                @if(session('success'))
                    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-emerald-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            <div>
                                <h3 class="text-sm font-semibold text-red-800">Please review the following errors:</h3>
                                <ul class="mt-1 list-disc list-inside text-xs text-red-700">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
