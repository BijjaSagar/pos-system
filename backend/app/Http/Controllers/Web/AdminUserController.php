<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $shop = Auth::user()->shop ?? Shop::first();
        $shopId = $shop ? $shop->id : 1;

        if (Auth::user()->isSuperAdmin() && !$request->filled('shop_id')) {
            $query = User::with('shop');
        } else {
            $query = User::where('shop_id', $shopId);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $totalUsers = User::where('shop_id', $shopId)->count();
        $userLimit = $shop && $shop->plan ? ($shop->plan->user_limit ?? 10) : 999;
        $canAddUser = $totalUsers < $userLimit;

        return view('admin.users.index', compact('users', 'shop', 'totalUsers', 'userLimit', 'canAddUser'));
    }

    public function create()
    {
        $shop = Auth::user()->shop ?? Shop::first();
        $shopId = $shop ? $shop->id : 1;
        $totalUsers = User::where('shop_id', $shopId)->count();
        $userLimit = $shop && $shop->plan ? ($shop->plan->user_limit ?? 10) : 999;

        if (!Auth::user()->isSuperAdmin() && $totalUsers >= $userLimit) {
            return redirect()->route('admin.users.index')
                ->with('error', "Your store has reached the plan limit of {$userLimit} users. Please upgrade your subscription plan to add more staff members.");
        }

        return view('admin.users.create', compact('shop'));
    }

    public function store(Request $request)
    {
        $shop = Auth::user()->shop ?? Shop::first();
        $shopId = $shop ? $shop->id : 1;
        $totalUsers = User::where('shop_id', $shopId)->count();
        $userLimit = $shop && $shop->plan ? ($shop->plan->user_limit ?? 10) : 999;

        if (!Auth::user()->isSuperAdmin() && $totalUsers >= $userLimit) {
            return redirect()->route('admin.users.index')
                ->with('error', "User limit of {$userLimit} reached. Upgrade plan to create more staff.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'role' => 'required|in:store_owner,manager,cashier,employee',
            'permissions' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $permissions = $request->input('permissions', User::defaultPermissionsForRole($validated['role']));

        User::create([
            'shop_id' => $shop->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
            'permissions' => $permissions,
        ]);

        return redirect()->route('admin.users.index')->with('success', "User '{$validated['name']}' created successfully as " . ucfirst(str_replace('_', ' ', $validated['role'])) . ".");
    }

    public function edit(User $user)
    {
        if (!Auth::user()->isSuperAdmin() && $user->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized access to user profile.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (!Auth::user()->isSuperAdmin() && $user->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized access to user profile.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:store_owner,manager,cashier,employee',
            'permissions' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        // Prevent current logged-in user from disabling or demoting themselves
        if ($user->id === Auth::id()) {
            $isActive = true;
            $role = Auth::user()->role;
        } else {
            $isActive = $request->boolean('is_active', true);
            $role = $validated['role'];
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->role = $role;
        $user->is_active = $isActive;

        if ($request->has('permissions')) {
            $user->permissions = $request->input('permissions', []);
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', "User '{$user->name}' updated successfully.");
    }

    public function destroy(User $user)
    {
        if (!Auth::user()->isSuperAdmin() && $user->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own logged-in user account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "User '{$name}' has been deleted.");
    }
}
