<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List users / staff for the authenticated user's shop.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // If super admin, allow viewing all users across shops
        if ($user->isSuperAdmin()) {
            $query = User::with('shop');
            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->shop_id);
            }
        } else {
            $query = User::where('shop_id', $user->shop_id);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Create a new staff / cashier / manager / store owner.
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
        ];

        if ($currentUser->isSuperAdmin()) {
            $rules['role'] = 'required|in:super_admin,store_owner,manager,cashier,employee';
            $rules['shop_id'] = 'nullable|exists:shops,id';
        } else {
            $rules['role'] = 'required|in:store_owner,manager,cashier,employee';
        }

        $validated = $request->validate($rules);

        $shopId = $currentUser->isSuperAdmin() 
            ? ($request->input('shop_id') ?? $currentUser->shop_id)
            : $currentUser->shop_id;

        // Check plan limits if creating for a store
        if ($shopId) {
            $shop = \App\Models\Shop::with('plan')->find($shopId);
            if ($shop && $shop->plan) {
                $userCount = User::where('shop_id', $shopId)->count();
                if ($userCount >= $shop->plan->user_limit) {
                    return response()->json([
                        'success' => false,
                        'message' => "Shop user limit of {$shop->plan->user_limit} reached. Upgrade plan to create more staff.",
                    ], 422);
                }
            }
        }

        $permissions = $request->input('permissions', User::defaultPermissionsForRole($validated['role']));

        $newUser = User::create([
            'shop_id' => $shopId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
            'permissions' => $permissions,
        ]);

        return response()->json([
            'success' => true,
            'message' => "User '{$newUser->name}' created successfully as " . ucfirst(str_replace('_', ' ', $newUser->role)),
            'data' => $newUser,
        ], 201);
    }

    /**
     * Show user details.
     */
    public function show(Request $request, $id)
    {
        $currentUser = $request->user();
        $user = User::with('shop')->findOrFail($id);

        if (!$currentUser->isSuperAdmin() && $user->shop_id !== $currentUser->shop_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user details / role / permissions / password.
     */
    public function update(Request $request, $id)
    {
        $currentUser = $request->user();
        $user = User::findOrFail($id);

        if (!$currentUser->isSuperAdmin() && $user->shop_id !== $currentUser->shop_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|required|in:super_admin,store_owner,manager,cashier,employee',
            'permissions' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        // Non-super-admins cannot make someone super_admin
        if (!$currentUser->isSuperAdmin() && isset($validated['role']) && $validated['role'] === 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Cannot assign super_admin role.'], 403);
        }

        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (array_key_exists('phone', $validated)) $user->phone = $validated['phone'];
        if (isset($validated['role']) && $user->id !== $currentUser->id) $user->role = $validated['role'];
        if ($request->has('is_active') && $user->id !== $currentUser->id) {
            $user->is_active = $request->boolean('is_active');
        }
        if ($request->has('permissions')) {
            $user->permissions = $request->input('permissions');
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => $user,
        ]);
    }

    /**
     * Delete a staff user.
     */
    public function destroy(Request $request, $id)
    {
        $currentUser = $request->user();
        $user = User::findOrFail($id);

        if (!$currentUser->isSuperAdmin() && $user->shop_id !== $currentUser->shop_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        if ($user->id === $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
