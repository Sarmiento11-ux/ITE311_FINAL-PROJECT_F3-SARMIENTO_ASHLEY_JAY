<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products with advanced filtering, search, sorting, and pagination.
     */
    public function index(Request $request)
    {
        $request->validate([
            'search'       => 'nullable|string|max:100',
            'category'     => 'nullable',
            'seller_id'    => 'nullable',
            'seller'       => 'nullable|string|max:100',
            'min_price'    => 'nullable|numeric|min:0',
            'max_price'    => 'nullable|numeric|min:0',
            'availability' => 'nullable|string|in:all,in_stock,out_of_stock',
            'in_stock'     => 'nullable|boolean',
            'status'       => 'nullable|string|in:active,inactive,all',
            'featured'     => 'nullable|boolean',
            'sort'         => 'nullable|string|max:50',
            'per_page'     => 'nullable|integer|min:1|max:100',
            'page'         => 'nullable|integer|min:1',
        ]);

        $query = Product::with(['category', 'seller:id,name,email,subscription_plan', 'seller.sellerProfile']);

        // Default to active products for public catalog unless requested
        if ($request->filled('status')) {
            if ($request->query('status') !== 'all') {
                $query->where('status', $request->query('status'));
            }
        } else {
            $query->where('status', 'active');
        }

        // 1. Advanced Search: product name, description, material, seller name, or artisan shop name
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('material', 'like', "%{$search}%")
                      ->orWhereHas('seller', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%")
                             ->orWhereHas('sellerProfile', function ($sp) use ($search) {
                                 $sp->where('shop_name', 'like', "%{$search}%");
                             });
                      });
                });
            }
        }

        // 2. Category Filter (id, slug, or name)
        if ($request->filled('category') && strtolower($request->query('category')) !== 'all') {
            $cat = $request->query('category');
            if (is_numeric($cat)) {
                $query->where('category_id', (int) $cat);
            } else {
                $query->whereHas('category', function ($q) use ($cat) {
                    $q->where('slug', $cat)->orWhere('name', $cat);
                });
            }
        }

        // 3. Seller Filter (by id or name/shop_name)
        if ($request->filled('seller_id') && strtolower((string)$request->query('seller_id')) !== 'all') {
            $query->where('seller_id', (int) $request->query('seller_id'));
        } elseif ($request->filled('seller') && strtolower($request->query('seller')) !== 'all') {
            $seller = trim($request->query('seller'));
            $query->whereHas('seller', function ($sq) use ($seller) {
                $sq->where('name', 'like', "%{$seller}%")
                   ->orWhereHas('sellerProfile', function ($sp) use ($seller) {
                       $sp->where('shop_name', 'like', "%{$seller}%");
                   });
            });
        }

        // 4. Price Bounds
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->query('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->query('max_price'));
        }

        // 5. Product Availability
        if ($request->filled('availability')) {
            $avail = strtolower($request->query('availability'));
            if ($avail === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($avail === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            }
        } elseif ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // 6. Featured products
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // 7. Sorting
        $sort = strtolower($request->query('sort', 'latest'));
        match ($sort) {
            'price_asc', 'price-low', 'price-low-high' => $query->orderBy('price', 'asc')->orderBy('id', 'asc'),
            'price_desc', 'price-high', 'price-high-low' => $query->orderBy('price', 'desc')->orderBy('id', 'desc'),
            'name_asc', 'name-a-z', 'alpha_asc' => $query->orderBy('name', 'asc'),
            'name_desc', 'name-z-a', 'alpha_desc' => $query->orderBy('name', 'desc'),
            'rating_desc', 'rating-high', 'top-rated' => $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating')->orderByDesc('id'),
            default => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
        };

        // 8. Pagination support
        $paginate = $request->has('paginate') ? $request->boolean('paginate') : true;

        if ($paginate) {
            $perPage = max(1, min(100, (int) $request->query('per_page', 9)));
            $page = max(1, (int) $request->query('page', 1));
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success'    => true,
                'message'    => 'Products retrieved successfully.',
                'data'       => $paginator->items(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'from'         => $paginator->firstItem(),
                    'to'           => $paginator->lastItem(),
                    'has_more'     => $paginator->hasMorePages(),
                ],
            ]);
        }

        $products = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data'    => $products,
        ]);
    }

    /**
     * Get dynamic filter options (categories, sellers, price range) from live MySQL records.
     *
     * GET /api/products/filters
     */
    public function filters()
    {
        $categories = \App\Models\Category::withCount(['products' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->map(fn($c) => [
                'id'             => $c->id,
                'name'           => $c->name,
                'slug'           => $c->slug,
                'products_count' => $c->products_count,
            ]);

        $sellers = \App\Models\User::where('role', 'seller')
            ->whereHas('products', fn($q) => $q->where('status', 'active'))
            ->with('sellerProfile')
            ->withCount(['products' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'name'           => $s->name,
                'shop_name'      => $s->sellerProfile?->shop_name ?? $s->name,
                'products_count' => $s->products_count,
            ]);

        $priceStats = Product::where('status', 'active')
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'categories' => $categories,
                'sellers'    => $sellers,
                'price'      => [
                    'min' => (float) ($priceStats->min_price ?? 0),
                    'max' => (float) ($priceStats->max_price ?? 1000),
                ],
            ],
        ]);
    }

    /**
     * Store a newly created product.
     * Supports multipart/form-data with image file or JSON body.
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
            'image'       => 'nullable', // string URL or file
            'images'      => 'nullable|array',
            'status'      => 'nullable|in:active,inactive,out_of_stock',
            'is_featured' => 'nullable|boolean',
            'material'    => 'nullable|string|max:255',
            'dimensions'  => 'nullable|string|max:255',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        }

        // Handle image file upload if provided
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => ['file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
            ], [
                'image.image' => 'The uploaded file must be a valid image.',
                'image.mimes' => 'Supported image formats are JPG, JPEG, PNG, and WEBP.',
                'image.max'   => 'Image size must not exceed 5MB.',
            ]);

            $file = $request->file('image');
            $safeFilename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
            $validated['image'] = $file->storeAs('products', $safeFilename, 'public');
        } elseif ($request->hasFile('image_file')) {
            $request->validate([
                'image_file' => ['file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
            ]);

            $file = $request->file('image_file');
            $safeFilename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
            $validated['image'] = $file->storeAs('products', $safeFilename, 'public');
        }

        $user = $request->user();
        // Seller listing limit enforcement (10 for Basic, unlimited for Pro)
        if ($user && $user->role === 'seller') {
            $isPro = $user->subscription_plan === 'pro'
                && ($user->subscription_status ?? 'active') === 'active'
                && (!$user->subscription_expires_at || $user->subscription_expires_at->isFuture());

            if (!$isPro) {
                $limit = $user->listing_limit ?? 10;
                $currentCount = Product::where('seller_id', $user->id)
                    ->where('status', '!=', 'archived')
                    ->count();

                if ($currentCount >= $limit) {
                    return response()->json([
                        'success' => false,
                        'message' => "You have reached your {$limit}-product listing limit on the Basic Artisan plan. Upgrade to Artisan Pro to unlock unlimited craft listings.",
                        'data'    => null,
                    ], 422);
                }
            }
        }

        // Seller ID is always the authenticated user (security enforcement)
        $validated['seller_id'] = $user ? $user->id : $request->user()->id;

        $product = Product::create($validated);
        $product->load(['category', 'seller:id,name,email,subscription_plan']);

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
        $product = Product::with(['category', 'seller:id,name,email,avatar,subscription_plan'])->find($id);

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

        // Authorization: Sellers can only edit their own products
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
            'image'       => 'nullable',
            'images'      => 'nullable|array',
            'status'      => 'nullable|in:active,inactive,out_of_stock',
            'is_featured' => 'nullable|boolean',
            'material'    => 'nullable|string|max:255',
            'dimensions'  => 'nullable|string|max:255',
        ]);

        // Handle image file upload if attached
        if ($request->hasFile('image') || $request->hasFile('image_file')) {
            $file = $request->file('image') ?? $request->file('image_file');

            $request->validate([
                'image'      => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
                'image_file' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
            ]);

            // Clean up previous image file from disk if locally stored
            $this->cleanupPhysicalImage($product->raw_image_path);

            $safeFilename = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
            $validated['image'] = $file->storeAs('products', $safeFilename, 'public');
        } elseif ($request->boolean('remove_image') || $request->input('image') === 'remove') {
            $this->cleanupPhysicalImage($product->raw_image_path);
            $validated['image'] = null;
        }

        $product->update($validated);
        $product->load(['category', 'seller:id,name,email,subscription_plan']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data'    => $product,
        ]);
    }

    /**
     * Upload / Replace product image.
     * POST /api/products/{id}/image
     */
    public function uploadImage(Request $request, $id)
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

        // Authorization check
        if ($user->role === 'seller' && $product->seller_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You can only manage images for your own craft listings.',
                'data'    => null,
            ], 403);
        }

        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
        ], [
            'image.required' => 'Please select an image file to upload.',
            'image.file'     => 'The uploaded file is not recognized.',
            'image.image'    => 'The file must be a valid image (JPEG, PNG, or WEBP).',
            'image.mimes'    => 'Unsupported format. Allowed formats: JPG, JPEG, PNG, WEBP.',
            'image.max'      => 'The image file size must not exceed 5MB.',
        ]);

        $file = $request->file('image');

        // Delete previous locally stored file
        $this->cleanupPhysicalImage($product->raw_image_path);

        // Generate safe unique filename to avoid path traversal and collision
        $extension = strtolower($file->getClientOriginalExtension());
        $safeFilename = Str::uuid() . '.' . $extension;
        $path = $file->storeAs('products', $safeFilename, 'public');

        $product->update(['image' => $path]);
        $product->load(['category', 'seller:id,name,email,subscription_plan']);

        return response()->json([
            'success' => true,
            'message' => 'Product image uploaded successfully.',
            'data'    => [
                'product'    => $product,
                'image_url'  => $product->image,
                'path'       => $path,
            ],
        ]);
    }

    /**
     * Delete product image.
     * DELETE /api/products/{id}/image
     */
    public function deleteImage(Request $request, $id)
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

        // Authorization check
        if ($user->role === 'seller' && $product->seller_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You can only remove images for your own craft listings.',
                'data'    => null,
            ], 403);
        }

        $this->cleanupPhysicalImage($product->raw_image_path);

        $product->update(['image' => null]);
        $product->load(['category', 'seller:id,name,email,subscription_plan']);

        return response()->json([
            'success' => true,
            'message' => 'Product image removed successfully.',
            'data'    => [
                'product'   => $product,
                'image_url' => $product->image, // returns fallback placeholder
            ],
        ]);
    }

    /**
     * Remove the specified product and any associated stored files.
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

        // Protect order history: If product was ever ordered, archive instead of cascading delete
        $hasOrders = \App\Models\OrderItem::where('product_id', $product->id)->exists();
        if ($hasOrders) {
            $product->update(['status' => 'archived']);
            return response()->json([
                'success' => true,
                'message' => 'Product has existing order history. Listing was safely archived instead of deleted to protect customer records.',
                'data'    => $product,
            ]);
        }

        // Clean up stored image file from disk
        $this->cleanupPhysicalImage($product->raw_image_path);

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
            'data'    => null,
        ]);
    }

    /**
     * Helper to safely remove physical file from storage disk.
     */
    private function cleanupPhysicalImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'products/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
