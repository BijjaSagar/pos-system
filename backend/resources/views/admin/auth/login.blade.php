<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Login - POS SAAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-[#5C06E8] to-[#4002AA] min-h-screen flex items-center justify-center p-6">
    <div class="max-w-4xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        <!-- Left Banner Illustration -->
        <div class="bg-purple-50 p-10 flex flex-col justify-between hidden md:flex border-r border-purple-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-[#5C06E8] flex items-center justify-center font-bold text-xl text-white shadow-md">
                    P
                </div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">POS SAAS</h2>
            </div>

            <div class="my-auto py-8 text-center">
                <div class="w-48 h-48 mx-auto bg-purple-100 rounded-full flex items-center justify-center text-[#5C06E8] mb-6 shadow-inner">
                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Retail & Inventory Suite</h3>
                <p class="text-sm text-gray-500 mt-2">Manage products, supplier orders, cash flow, and thermal receipt printing in real-time.</p>
            </div>

            <div class="text-xs text-gray-400">
                &copy; {{ date('Y') }} POS SAAS Cloud Platform.
            </div>
        </div>

        <!-- Right Login Form -->
        <div class="p-10 flex flex-col justify-center">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Welcome to <span class="text-[#5C06E8]">POS SAAS</span></h2>
                <p class="text-sm text-gray-500 mt-1">Welcome back, please sign in to your store account.</p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email', 'store@pospro.app') }}" required
                            class="w-full px-4 py-3 pl-11 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent text-sm"
                            placeholder="Enter your email address">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" value="112233" required
                            class="w-full px-4 py-3 pl-11 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent text-sm"
                            placeholder="Enter your password">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-gray-600">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#5C06E8] focus:ring-purple-500 border-gray-300">
                        <span class="ml-2 text-xs">Remember me</span>
                    </label>
                    <a href="#" class="text-xs text-[#5C06E8] font-semibold hover:underline">Forgot password?</a>
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#5C06E8] hover:bg-[#4A00B0] text-white font-semibold rounded-xl shadow-lg shadow-purple-500/25 transition">
                    Sign In to Portal
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-500">Quick Test Credentials:</p>
                <div class="mt-2 flex justify-center space-x-2">
                    <span class="px-2 py-1 bg-gray-100 rounded text-xs text-gray-700">store@pospro.app</span>
                    <span class="px-2 py-1 bg-gray-100 rounded text-xs text-gray-700">112233</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
