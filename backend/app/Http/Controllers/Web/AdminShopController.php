<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminShopController extends Controller
{
    /**
     * Display a listing of all shops/stores in the system.
     */
    public function index(Request $request)
    {
        $query = Shop::withCount(['products', 'sales', 'users'])->with('plan');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $shops = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        return view('admin.shops.index', compact('shops'));
    }

    /**
     * Show the form for creating a new shop and its owner account.
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('admin.shops.create', compact('plans'));
    }

    /**
     * Store a newly created shop and its owner account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Store details
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'tax_number' => 'nullable|string|max:50',
            'currency_symbol' => 'required|string|max:10',
            'currency_code' => 'required|string|max:10',
            'thermal_paper_size' => 'required|in:mm58,mm80',
            'primary_color' => 'nullable|string',
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:500',
            'plan_id' => 'nullable|exists:plans,id',

            // Store Owner Credentials
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255|unique:users,email',
            'owner_password' => 'required|string|min:6',
        ]);

        $defaultPlan = Plan::first();

        DB::beginTransaction();
        try {
            // 1. Create Shop
            $shop = Shop::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? $validated['owner_email'],
                'address' => $validated['address'] ?? null,
                'tax_number' => $validated['tax_number'] ?? null,
                'currency_symbol' => $validated['currency_symbol'] ?? '₹',
                'currency_code' => $validated['currency_code'] ?? 'INR',
                'currency_position' => 'prefix',
                'thermal_paper_size' => $validated['thermal_paper_size'] ?? 'mm58',
                'primary_color' => $validated['primary_color'] ?? '#1E3A8A',
                'receipt_header' => $validated['receipt_header'] ?? 'Welcome to ' . $validated['name'],
                'receipt_footer' => $validated['receipt_footer'] ?? 'Thank you for visiting! Please visit again.',
                'plan_id' => $validated['plan_id'] ?? ($defaultPlan?->id ?? null),
                'plan_expires_at' => now()->addYear(),
                'status' => 'active',
            ]);

            // 2. Create Store Owner User
            $owner = User::create([
                'shop_id' => $shop->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['owner_password']),
                'role' => 'store_owner',
                'is_active' => true,
                'permissions' => User::defaultPermissionsForRole('store_owner'),
            ]);

            // 3. Create Default Categories for the new shop
            $defaultCategories = ['Hot Beverages', 'Cold Beverages', 'Snacks & Bites', 'Special Combos'];
            foreach ($defaultCategories as $catName) {
                Category::firstOrCreate([
                    'name' => $catName,
                ], [
                    'slug' => Str::slug($catName),
                ]);
            }

            DB::commit();

            // Set session flash with newly created credentials to easily copy & share
            return redirect()->route('admin.shops.index')->with([
                'success' => "New store '{$shop->name}' created successfully!",
                'new_store_credentials' => [
                    'shop_name' => $shop->name,
                    'email' => $validated['owner_email'],
                    'password' => $validated['owner_password'],
                    'role' => 'Store Owner',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create shop: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified shop.
     */
    public function edit(Shop $shop)
    {
        $plans = Plan::where('is_active', true)->get();
        return view('admin.shops.edit', compact('shop', 'plans'));
    }

    /**
     * Update the specified shop.
     */
    public function update(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'tax_number' => 'nullable|string|max:50',
            'currency_symbol' => 'required|string|max:10',
            'currency_code' => 'required|string|max:10',
            'thermal_paper_size' => 'required|in:mm58,mm80',
            'primary_color' => 'nullable|string',
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $shop->update($validated);

        return redirect()->route('admin.shops.index')->with('success', "Store '{$shop->name}' updated successfully.");
    }
}
