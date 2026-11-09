<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users with search and filtering.
     */
    public function index(Request $request)
    {
        $query = User::with('sellerProfile')->withCount(['orders', 'products']);

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->has('is_active') && $request->query('is_active') !== '') {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully.',
            'data'    => $users,
        ]);
    }

    /**
     * Display the specified user with profile and order stats.
     */
    public function show($id)
    {
        $user = User::with(['sellerProfile', 'orders.items.product', 'products'])->withCount(['orders', 'products'])->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully.',
            'data'    => $user,
        ]);
    }

    /**
     * Update user profile information.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'email'     => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|string',
            'avatar'    => 'nullable|string',
            'role'      => 'sometimes|required|in:customer,seller,admin',
            'is_active' => 'sometimes|boolean',
            'password'  => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data'    => $user->load('sellerProfile'),
        ]);
    }

    /**
     * Toggle active/inactive status for a user.
     */
    public function toggleActive(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'data'    => null,
            ], 404);
        }

        // Security: Prevent administrators from deactivating their own active account
        if ($request->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own administrative account.',
                'data'    => null,
            ], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        // If deactivated, revoke all active Sanctum tokens
        if (!$user->is_active) {
            $user->tokens()->delete();
        }

        $statusStr = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "User account has been {$statusStr}.",
            'data'    => $user,
        ]);
    }

    /**
     * Store a newly created user (Admin User Creation).
     * POST /api/users
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'nullable|string|min:3|max:50|unique:users,username',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'role'      => 'required|in:customer,seller,admin',
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'shop_name' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $user = User::create([
            'name'      => $validated['name'],
            'username'  => $validated['username'] ?? null,
            'email'     => $validated['email'],
            'password'  => $validated['password'],
            'role'      => $validated['role'],
            'phone'     => $validated['phone'] ?? null,
            'address'   => $validated['address'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        if ($user->role === 'seller') {
            $shopName = $validated['shop_name'] ?? ($user->name . "'s Workshop");
            $user->sellerProfile()->create([
                'shop_name'           => $shopName,
                'shop_slug'           => \Illuminate\Support\Str::slug($shopName) . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5)),
                'shop_description'    => 'Local handcrafted artisan workshop.',
                'contact_number'      => $user->phone ?? '',
                'address'             => $user->address ?? '',
                'is_verified'         => false,
                'verification_status' => 'pending',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data'    => $user->load('sellerProfile'),
        ], 201);
    }

    /**
     * Delete user account permanently or safely archive.
     * DELETE /api/users/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'data'    => null,
            ], 404);
        }

        if ($request->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own active administrator account.',
                'data'    => null,
            ], 422);
        }

        $user->tokens()->delete();

        // If user has orders or products, soft-deactivate to preserve historical integrity
        if ($user->orders()->exists() || $user->products()->exists()) {
            $user->update(['is_active' => false]);
            return response()->json([
                'success' => true,
                'message' => 'User has active transactions or catalog items. Account was safely deactivated and archived.',
                'data'    => $user,
            ]);
        }

        if ($user->sellerProfile) {
            $user->sellerProfile()->delete();
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User account deleted successfully.',
            'data'    => null,
        ]);
    }
}
