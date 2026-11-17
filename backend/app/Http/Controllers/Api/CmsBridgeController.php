<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\SellerVerificationService;
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

    /** Verification statuses a CMS editor is allowed to set. */
    private const VERIFICATION_STATUSES = ['pending', 'approved', 'rejected'];

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

    /**
     * GET /api/cms-bridge/categories
     *
     * Catalog taxonomy snapshot for the CMS. Includes the live product count so
     * editors can see the impact of a delete before attempting it.
     */
    public function categories(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,inactive,all',
        ]);

        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $status = $request->query('status', 'all');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $categories = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'CMS category taxonomy retrieved successfully.',
            'data'    => $categories,
            'meta'    => [
                'count' => $categories->count(),
            ],
        ]);
    }

    /**
     * POST /api/cms-bridge/categories
     *
     * Create a marketplace category from the CMS.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $category = Category::create($validated);

        Log::info('CMS bridge created a category', [
            'category_id' => $category->id,
            'slug'        => $category->slug,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data'    => $category->fresh()->loadCount('products'),
        ], 201);
    }

    /**
     * PUT /api/cms-bridge/categories/{id}
     *
     * Update category taxonomy details.
     */
    public function updateCategory(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'slug'        => 'sometimes|required|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $category->update($validated);

        Log::info('CMS bridge updated a category', [
            'category_id' => $category->id,
            'slug'        => $category->slug,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data'    => $category->fresh()->loadCount('products'),
        ]);
    }

    /**
     * DELETE /api/cms-bridge/categories/{id}
     *
     * Delete an empty category. Categories that still own products are blocked
     * so the CMS can never orphan live listings.
     */
    public function deleteCategory($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
                'data'    => null,
            ], 404);
        }

        $productCount = $category->products()->count();

        if ($productCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete category '{$category->name}'. It is currently linked to {$productCount} product(s). Please reassign or delete those products first.",
                'data'    => [
                    'product_count' => $productCount,
                ],
            ], 422);
        }

        $category->delete();

        Log::info('CMS bridge deleted a category', [
            'category_id' => $category->id,
            'slug'        => $category->slug,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
            'data'    => null,
        ]);
    }

    /**
     * GET /api/cms-bridge/sellers
     *
     * Artisan storefront applications for CMS moderation. Includes the profile
     * details moderators need to approve or reject a workshop.
     */
    public function sellers(Request $request)
    {
        $request->validate([
            'search'              => 'nullable|string|max:100',
            'verification_status' => 'nullable|string|in:pending,approved,rejected',
            'is_active'           => 'nullable|boolean',
        ]);

        $query = User::where('role', 'seller')
            ->with('sellerProfile')
            ->withCount('products');

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('sellerProfile', function ($sq) use ($search) {
                      $sq->where('shop_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('verification_status')) {
            $status = (string) $request->query('verification_status');
            $query->whereHas('sellerProfile', function ($sq) use ($status) {
                $sq->where('verification_status', $status);
            });
        }

        if ($request->has('is_active') && $request->query('is_active') !== '') {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $sellers = $query->orderBy('created_at', 'desc')->get()->map(function (User $seller) {
            $profile = $seller->sellerProfile;

            return [
                'id'             => $seller->id,
                'name'           => $seller->name,
                'email'          => $seller->email,
                'phone'          => $seller->phone ?? $profile?->contact_number,
                'is_active'      => (bool) $seller->is_active,
                'products_count' => $seller->products_count,
                'created_at'     => $seller->created_at?->toISOString(),
                'shop_name'      => $profile?->shop_name ?? ($seller->name . "'s Workshop"),
                'verification_status' => $profile?->verification_status ?? 'pending',
                'verification_notes'  => $profile?->verification_notes,
                'verified_at'         => $profile?->verified_at?->toISOString(),
                'seller_profile' => $profile ? [
                    'id'                       => $profile->id,
                    'shop_name'                => $profile->shop_name,
                    'shop_slug'                => $profile->shop_slug,
                    'shop_description'         => $profile->shop_description,
                    'location'                 => $profile->location,
                    'address'                  => $profile->address,
                    'contact_number'           => $profile->contact_number,
                    'is_verified'              => (bool) $profile->is_verified,
                    'is_active'                => (bool) $profile->is_active,
                    'verification_status'      => $profile->verification_status ?? 'pending',
                    'verification_notes'       => $profile->verification_notes,
                    'verification_reviewed_at' => $profile->verification_reviewed_at?->toISOString(),
                    'verified_at'              => $profile->verified_at?->toISOString(),
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'CMS artisan storefronts retrieved successfully.',
            'data'    => $sellers,
            'meta'    => [
                'count'     => $sellers->count(),
                'statuses'  => self::VERIFICATION_STATUSES,
            ],
        ]);
    }

    /**
     * PATCH /api/cms-bridge/sellers/{id}/verification
     *
     * Approve, reject, or return an artisan storefront to review from the CMS.
     * WordPress is the moderation authority; Laravel keeps the transactional
     * profile state and sends the seller notification email.
     */
    public function updateSellerVerification(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', self::VERIFICATION_STATUSES),
            'notes'  => 'nullable|string|max:2000',
        ]);

        $seller = User::where('role', 'seller')->with('sellerProfile')->find($id);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found.',
                'data'    => null,
            ], 404);
        }

        if (!$seller->sellerProfile) {
            return response()->json([
                'success' => false,
                'message' => 'This seller has no storefront profile to review.',
                'data'    => null,
            ], 422);
        }

        $status = $validated['status'];
        $data = SellerVerificationService::update($seller, $status, $validated['notes'] ?? null);

        Log::info('CMS bridge reviewed an artisan storefront', [
            'seller_id' => $seller->id,
            'status'    => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Artisan storefront {$status} successfully.",
            'data'    => $data,
        ]);
    }
}
