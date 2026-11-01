<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products with filtering, search, and sorting.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'seller:id,name,email']);

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category id or slug
        if ($request->filled('category')) {
            $cat = $request->query('category');
            if (is_numeric($cat)) {
                $query->where('category_id', $cat);
            } else {
                $query->whereHas('category', function ($q) use ($cat) {
                    $q->where('slug', $cat)->orWhere('name', 'like', "%{$cat}%");
                });
            }
        }

        // Filter by seller id
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->query('seller_id'));
        }

        // Filter by status (default to active for public catalog unless requested)
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Sorting
        $sort = $request->query('sort', 'latest');
        match ($sort) {
            'price_asc', 'price-low'   => $query->orderBy('price', 'asc'),
            'price_desc', 'price-high' => $query->orderBy('price', 'desc'),
            'name_asc'                 => $query->orderBy('name', 'asc'),
            'name_desc'                => $query->orderBy('name', 'desc'),
            default                    => $query->orderBy('created_at', 'desc'),
        };

        // Pagination or all
        if ($request->boolean('paginate', false)) {
            $perPage = (int) $request->query('per_page', 12);
            $products = $query->paginate($perPage);
        } else {
            $products = $query->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data'    => $products,
        ]);
    }

    /**
     * Store a newly created product.
     * Seller is always the authenticated user (not from request body).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable|string',
            'images'      => 'nullable|array',
            'status'      => 'nullable|in:active,inactive,out_of_stock',
            'is_featured' => 'nullable|boolean',
            'material'    => 'nullable|string|max:255',
            'dimensions'  => 'nullable|string|max:255',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        }

        // Seller ID is always the authenticated user (security: never trust client-supplied seller_id)
        $validated['seller_id'] = $request->user()->id;

        $product = Product::create($validated);
        $product->load(['category', 'seller:id,name,email']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data'    => $product,
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'seller:id,name,email,avatar'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data'    => $product,
        ]);
    }

    /**
     * Update the specified product.
     * Sellers may only update their own products. Admins can update any.
     */
    public function update(Request $request, $id)
    {
        $user    = $request->user();
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
                'data'    => null,
            ], 404);
        }

        // Sellers can only edit their own products
        if ($user->role === 'seller' && $product->seller_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You can only edit your own products.',
                'data'    => null,
            ], 403);
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name'        => 'sometimes|required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'price'       => 'sometimes|required|numeric|min:0',
            'stock'       => 'sometimes|required|integer|min:0',
            'image'       => 'nullable|string',
            'images'      => 'nullable|array',
            'status'      => 'nullable|in:active,inactive,out_of_stock',
            'is_featured' => 'nullable|boolean',
            'material'    => 'nullable|string|max:255',
            'dimensions'  => 'nullable|string|max:255',
        ]);

        $product->update($validated);
        $product->load(['category', 'seller:id,name,email']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data'    => $product,
        ]);
    }

    /**
     * Remove the specified product.
     * Sellers may only delete their own products. Admins can delete any.
     */
    public function destroy(Request $request, $id)
    {
        $user    = $request->user();
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
                'data'    => null,
            ], 404);
        }

        // Sellers can only delete their own products
        if ($user->role === 'seller' && $product->seller_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You can only delete your own products.',
                'data'    => null,
            ], 403);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
            'data'    => null,
        ]);
    }
}
