<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    // ─── Public: GET /api/products/{productId}/reviews ──────────────────────

    /**
     * Return all reviews for a product with summary stats.
     * Public endpoint — no auth required.
     */
    public function index(int $productId)
    {
        $product = Product::findOrFail($productId);

        $reviews = ProductReview::with(['user:id,name'])
            ->where('product_id', $productId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'user_id'     => $r->user_id,
                'user_name'   => $r->user?->name ?? 'Anonymous',
                'rating'      => $r->rating,
                'comment'     => $r->comment,
                'created_at'  => $r->created_at?->toDateString(),
            ]);

        $avg   = $reviews->count() ? round($reviews->avg('rating'), 1) : 0;
        $count = $reviews->count();

        return response()->json([
            'success'      => true,
            'avg_rating'   => $avg,
            'review_count' => $count,
            'data'         => $reviews,
        ]);
    }

    // ─── Auth: POST /api/products/{productId}/reviews ───────────────────────

    /**
     * Submit a new review for a product.
     * Requires: authenticated customer, completed order item for this product.
     */
    public function store(Request $request, int $productId)
    {
        // 1. Validate input attributes first
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // 2. Must be a customer
        if ($user->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Only customers can submit product reviews.',
            ], 403);
        }

        $product = Product::findOrFail($productId);

        // 3. Find a qualifying delivered/completed order item for this customer + product
        $orderItem = OrderItem::query()
            ->where('product_id', $productId)
            ->whereHas('order', fn($q) => $q
                ->where('user_id', $user->id)
                ->whereIn('status', ['delivered', 'completed'])
            )
            ->first();

        if (!$orderItem) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review products from completed orders.',
            ], 403);
        }

        // 4. Duplicate prevention
        if (ProductReview::where('user_id', $user->id)->where('product_id', $productId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        $review = ProductReview::create([
            'user_id'       => $user->id,
            'product_id'    => $productId,
            'order_item_id' => $orderItem->id,
            'rating'        => $validated['rating'],
            'comment'       => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully.',
            'data'    => [
                'id'         => $review->id,
                'user_name'  => $user->name,
                'rating'     => $review->rating,
                'comment'    => $review->comment,
                'created_at' => $review->created_at?->toDateString(),
            ],
        ], 201);
    }

    // ─── Auth: PUT /api/products/{productId}/reviews/{reviewId} ─────────────

    /**
     * Edit own review. Customers may only edit their own reviews.
     */
    public function update(Request $request, int $productId, int $reviewId)
    {
        $user   = Auth::user();
        $review = ProductReview::where('product_id', $productId)->findOrFail($reviewId);

        // Ownership check
        if ($review->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own reviews.',
            ], 403);
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Review updated.',
            'data'    => [
                'id'         => $review->id,
                'rating'     => $review->rating,
                'comment'    => $review->comment,
                'created_at' => $review->created_at?->toDateString(),
            ],
        ]);
    }

    // ─── Auth: DELETE /api/products/{productId}/reviews/{reviewId} ──────────

    /**
     * Delete a review.
     * Customers may delete their own; admins may delete any.
     */
    public function destroy(int $productId = 0, int $reviewId = 0)
    {
        $user  = Auth::user();
        $id    = $reviewId > 0 ? $reviewId : $productId;
        $query = ProductReview::query();

        if ($productId > 0 && $reviewId > 0) {
            $query->where('product_id', $productId);
        }

        $review = $query->findOrFail($id);

        if ($review->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this review.',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review removed successfully.',
        ]);
    }

    // ─── Seller: GET /api/seller/reviews ────────────────────────────────────

    /**
     * Return all reviews for the authenticated seller's products.
     */
    public function sellerReviews(Request $request)
    {
        $seller = Auth::user();

        $reviews = ProductReview::with(['user:id,name', 'product:id,name'])
            ->whereHas('product', fn($q) => $q->where('seller_id', $seller->id))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'product_id'   => $r->product_id,
                'product_name' => $r->product?->name,
                'user_name'    => $r->user?->name ?? 'Anonymous',
                'rating'       => $r->rating,
                'comment'      => $r->comment,
                'created_at'   => $r->created_at?->toDateString(),
            ]);

        return response()->json([
            'success' => true,
            'data'    => $reviews,
        ]);
    }

    // ─── Admin: GET /api/admin/reviews ──────────────────────────────────────

    /**
     * Return all reviews in the marketplace (admin oversight).
     */
    public function adminIndex(Request $request)
    {
        $reviews = ProductReview::with(['user:id,name', 'product:id,name'])
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $reviews->map(fn($r) => [
                'id'           => $r->id,
                'product_id'   => $r->product_id,
                'product_name' => $r->product?->name,
                'user_id'      => $r->user_id,
                'user_name'    => $r->user?->name ?? 'Anonymous',
                'rating'       => $r->rating,
                'comment'      => $r->comment,
                'created_at'   => $r->created_at?->toDateString(),
            ]),
            'total' => $reviews->total(),
        ]);
    }

    // ─── Auth: GET /api/products/{productId}/reviews/my ─────────────────────

    /**
     * Return the authenticated user's review for a specific product (if any).
     */
    public function myReview(int $productId)
    {
        $user   = Auth::user();
        $review = ProductReview::where('user_id', $user->id)
                               ->where('product_id', $productId)
                               ->first();

        return response()->json([
            'success' => true,
            'data'    => $review ? [
                'id'         => $review->id,
                'rating'     => $review->rating,
                'comment'    => $review->comment,
                'created_at' => $review->created_at?->toDateString(),
            ] : null,
        ]);
    }

    // ─── Auth: GET /api/products/{productId}/reviews/eligibility ────────────

    /**
     * Check if the authenticated user is eligible to review a product.
     * Eligible = has a delivered/completed order item for this product.
     */
    public function checkEligibility(int $productId)
    {
        $user = Auth::user();

        if ($user->role !== 'customer') {
            return response()->json(['eligible' => false, 'reason' => 'Only customers can review products.']);
        }

        $hasCompletedOrder = OrderItem::query()
            ->where('product_id', $productId)
            ->whereHas('order', fn($q) => $q
                ->where('user_id', $user->id)
                ->whereIn('status', ['delivered', 'completed'])
            )
            ->exists();

        if (!$hasCompletedOrder) {
            return response()->json(['eligible' => false, 'reason' => 'Purchase this product and complete the order first.']);
        }

        $alreadyReviewed = ProductReview::where('user_id', $user->id)
                                         ->where('product_id', $productId)
                                         ->exists();

        if ($alreadyReviewed) {
            return response()->json(['eligible' => false, 'reason' => 'already_reviewed']);
        }

        return response()->json(['eligible' => true, 'reason' => null]);
    }
}
