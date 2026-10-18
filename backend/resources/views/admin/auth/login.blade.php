<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Login - POS Enterprise SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#1E3A8A] min-h-screen flex items-center justify-center p-6">
    <div class="max-w-4xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        <!-- Left Banner Illustration -->
        <div class="bg-slate-900 p-10 flex flex-col justify-between hidden md:flex border-r border-slate-800 text-white">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-xl text-white shadow-md">
                    P
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-tight">POS Enterprise</h2>
                    <p class="text-[11px] text-blue-300 font-medium">Cloud Multi-Store Management</p>
                </div>
            </div>

            <div class="my-auto py-8 text-center">
                <div class="w-44 h-44 mx-auto bg-slate-800/80 border border-slate-700/60 rounded-3xl flex items-center justify-center text-blue-400 mb-6 shadow-xl">
                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white">Retail, POS & Inventory</h3>
                <p class="text-sm text-slate-300 mt-2">Manage products, procurement, multi-tier pricing, and bluetooth thermal receipts in real-time.</p>
            </div>

            <div class="text-xs text-slate-400">
                &copy; {{ date('Y') }} POS Enterprise SaaS • Multi-Store Architecture.
            </div>
        </div>

        <!-- Right Login Form -->
        <div class="p-10 flex flex-col justify-center bg-white">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-slate-900">Sign In to <span class="text-blue-700">Terminal</span></h2>
                <p class="text-sm text-slate-500 mt-1">Enter your store administrator or cashier credentials.</p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Email Address</label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3 pl-11 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm"
                            placeholder="Enter your registered email">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 pl-11 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm"
                            placeholder="Enter your account password">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-slate-600">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="ml-2 text-xs">Remember me</span>
                    </label>
                    <a href="#" class="text-xs text-blue-600 font-semibold hover:underline">Forgot password?</a>
                </div>

                <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/25 transition">
                    Sign In to Portal
                </button>
            </form>
        </div>
    </div>
</body>
</html>
