<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Register a new user (customer or seller).
     *
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => ['required', 'confirmed', Password::min(6)],
            'role'                  => 'nullable|in:customer,seller',
            // Seller profile fields (optional)
            'shop_name'             => 'nullable|string|max:255',
            'shop_description'      => 'nullable|string',
            'contact_number'        => 'nullable|string|max:50',
            'address'               => 'nullable|string',
        ]);

        // Default role to 'customer' if not provided or invalid
        $role = $validated['role'] ?? 'customer';

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'], // hashed automatically via cast
            'role'     => $role,
        ]);

        // If registering as seller, create seller profile
        if ($role === 'seller') {
            $user->sellerProfile()->create([
                'shop_name'        => $validated['shop_name'] ?? $validated['name'] . "'s Shop",
                'shop_description' => $validated['shop_description'] ?? '',
                'contact_number'   => $validated['contact_number'] ?? '',
                'address'          => $validated['address'] ?? '',
            ]);
        }

        // Issue Sanctum token
        $token = $user->createToken('craftmarket-auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully. Welcome to CraftMarket!',
            'data'    => [
                'user'  => $user->load('sellerProfile'),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login with email and password.
     *
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password. Please check your credentials and try again.',
                'data'    => null,
            ], 401);
        }

        // Revoke existing tokens for this device (clean session)
        $user->tokens()->where('name', 'craftmarket-auth')->delete();

        // Issue new token
        $token = $user->createToken('craftmarket-auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful. Welcome back, ' . $user->name . '!',
            'data'    => [
                'user'  => $user->load('sellerProfile'),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout — revoke the current token.
     *
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        // Revoke the token used for this request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
            'data'    => null,
        ]);
    }

    /**
     * Get the currently authenticated user.
     *
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Authenticated user retrieved.',
            'data'    => $request->user()->load('sellerProfile'),
        ]);
    }

    /**
     * Update the authenticated user's profile.
     *
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'avatar'  => 'nullable|string|max:500',
            // Seller profile fields
            'shop_name'        => 'nullable|string|max:255',
            'shop_description' => 'nullable|string',
            'contact_number'   => 'nullable|string|max:50',
        ]);

        // Update base user fields
        $user->update(array_filter([
            'name'    => $validated['name'] ?? null,
            'phone'   => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'avatar'  => $validated['avatar'] ?? null,
        ], fn($v) => $v !== null));

        // Update seller profile if seller
        if ($user->role === 'seller' && $user->sellerProfile) {
            $sellerData = array_filter([
                'shop_name'        => $validated['shop_name'] ?? null,
                'shop_description' => $validated['shop_description'] ?? null,
                'contact_number'   => $validated['contact_number'] ?? null,
            ], fn($v) => $v !== null);

            if (!empty($sellerData)) {
                $user->sellerProfile()->update($sellerData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => $user->fresh('sellerProfile'),
        ]);
    }

    /**
     * Change the authenticated user's password.
     *
     * PUT /api/auth/password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(6)],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
                'data'    => null,
            ], 422);
        }

        $user->update(['password' => $validated['password']]);

        // Revoke all tokens to force re-login
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully. Please log in again.',
            'data'    => null,
        ]);
    }
}
