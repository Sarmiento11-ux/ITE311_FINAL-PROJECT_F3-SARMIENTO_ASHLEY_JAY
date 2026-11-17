<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Write-side of the headless WordPress CMS bridge.
 *
 * Every route in this controller is protected by the `cms.bridge`
 * middleware, which requires the shared X-CMS-Bridge-Key secret. The bridge
 * intentionally exposes only catalog moderation and account activation so
 * WordPress can never touch orders, payments, escrow, or payouts.
 */
class CmsBridgeController extends Controller
{
    /** Product statuses a CMS editor is allowed to set. */
    private const PRODUCT_STATUSES = ['active', 'inactive', 'out_of_stock', 'archived'];

    /**
     * GET /api/cms-bridge/products
     *
     * Catalog snapshot for the CMS. Unlike the public storefront this includes
     * listings from unverified artisans so editors can moderate everything.
     */
    public function products(Request $request)
    {
        $request->validate([
            'search'    => 'nullable|string|max:100',
            'status'    => 'nullable|string|in:active,inactive,out_of_stock,archived,all',
            'seller_id' => 'nullable|integer|min:1',
            'per_page'  => 'nullable|integer|min:1|max:200',
        ]);

        $query = Product::with([
            'category',
            'seller:id,name,email,is_active',
            'seller.sellerProfile',
        ]);

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhereHas('seller', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status') && $request->query('status') !== 'all') {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', (int) $request->query('seller_id'));
        }

        $perPage = (int) $request->query('per_page', 100);
        $products = $query->orderBy('created_at', 'desc')->limit($perPage)->get();

        return response()->json([
            'success' => true,
            'message' => 'CMS product catalog retrieved successfully.',
            'data'    => $products,
            'meta'    => [
                'count'     => $products->count(),
                'per_page'  => $perPage,
                'statuses'  => self::PRODUCT_STATUSES,
            ],
        ]);
    }

    /**
     * PATCH /api/cms-bridge/products/{id}/status
     *
     * Moderate a listing without granting the CMS access to pricing, stock, or
     * ownership fields.
     */
    public function updateProductStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', self::PRODUCT_STATUSES),
        ]);

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
                'data'    => null,
            ], 404);
        }

        $previous = $product->status;
        $product->status = $validated['status'];
        $product->save();

        Log::info('CMS bridge changed product status', [
            'product_id' => $product->id,
            'from'       => $previous,
            'to'         => $product->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Product status updated to {$product->status}.",
            'data'    => $product->fresh(['category', 'seller:id,name,email,is_active']),
        ]);
    }

    /**
     * PATCH /api/cms-bridge/users/{id}/toggle-active
     *
     * Activate or deactivate a marketplace account. Deactivation revokes every
     * Sanctum token so an already signed-in session cannot keep using the API.
     * Administrator accounts are protected from deactivation.
     */
    public function toggleUser(Request $request, $id)
    {
        $validated = $request->validate([
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'data'    => null,
            ], 404);
        }

        $targetState = array_key_exists('is_active', $validated) && $validated['is_active'] !== null
            ? (bool) $validated['is_active']
            : !$user->is_active;

        if ($user->role === 'admin' && $targetState === false) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator accounts cannot be deactivated from the CMS bridge.',
                'data'    => null,
            ], 422);
        }

        $user->is_active = $targetState;
        $user->save();

        $revokedTokens = 0;
        if (!$user->is_active) {
            $revokedTokens = $user->tokens()->delete();
        }

        Log::info('CMS bridge changed user activation', [
            'user_id'        => $user->id,
            'role'           => $user->role,
            'is_active'      => $user->is_active,
            'revoked_tokens' => $revokedTokens,
        ]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active
                ? 'User account has been activated.'
                : 'User account has been deactivated and signed out.',
            'data'    => [
                'user'           => $user->fresh(['sellerProfile']),
                'revoked_tokens' => $revokedTokens,
            ],
        ]);
    }
}
