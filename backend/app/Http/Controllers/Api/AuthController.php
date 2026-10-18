<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is deactivated. Please contact support.',
            ], 403);
        }

        $shop = null;
        if ($user->shop_id) {
            $shop = Shop::with('plan')->find($user->shop_id);
            if ($shop && $shop->status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your shop subscription has been suspended. Please contact the administrator.',
                ], 403);
            }
        }

        $token = $user->createToken('pos_app_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'shop_id' => $user->shop_id,
            ],
            'shop' => $shop,
        ]);
    }

    public function registerShop(Request $request)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string',
            'currency_symbol' => 'nullable|string|max:5',
        ]);

        $shop = Shop::create([
            'name' => $request->shop_name,
            'plan_id' => 1, // Free trial default
            'plan_expires_at' => now()->addDays(14),
            'phone' => $request->phone,
            'email' => $request->email,
            'currency_symbol' => $request->currency_symbol ?? '₹',
            'status' => 'active',
        ]);

        $user = User::create([
            'shop_id' => $shop->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'store_owner',
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        // Create default warehouse
        Warehouse::create([
            'shop_id' => $shop->id,
            'name' => 'Main Warehouse',
            'is_default' => true,
        ]);

        $token = $user->createToken('pos_app_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Shop and account registered successfully',
            'token' => $token,
            'user' => $user,
            'shop' => $shop->load('plan'),
        ], 201);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('shop.plan');
        return response()->json([
            'success' => true,
            'user' => $user,
            'shop' => $user->shop,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
