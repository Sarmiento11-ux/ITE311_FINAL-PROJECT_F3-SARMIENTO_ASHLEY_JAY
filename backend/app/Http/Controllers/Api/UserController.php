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
}
