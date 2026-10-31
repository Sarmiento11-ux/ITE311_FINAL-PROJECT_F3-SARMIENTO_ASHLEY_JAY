<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user:id,name,email', 'items.product']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'data'    => $orders,
        ]);
    }

    /**
     * Store a newly created order with items.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'shipping_fee'     => 'nullable|numeric|min:0',
            'payment_method'   => 'nullable|string|max:50',
            'shipping_address' => 'required|string',
            'contact_number'   => 'required|string|max:50',
            'notes'            => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id'   => $product->id,
                    'seller_id'    => $product->seller_id,
                    'product_name' => $product->name,
                    'unit_price'   => $product->price,
                    'quantity'     => $item['quantity'],
                    'subtotal'     => $itemSubtotal,
                ];

                // Deduct stock if sufficient
                if ($product->stock >= $item['quantity']) {
                    $product->decrement('stock', $item['quantity']);
                }
            }

            $shippingFee = $validated['shipping_fee'] ?? 0;
            $totalAmount = $subtotal + $shippingFee;

            $order = Order::create([
                'user_id'          => $validated['user_id'],
                'order_number'     => 'CM-' . strtoupper(Str::random(8)),
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'total_amount'     => $totalAmount,
                'status'           => 'pending',
                'payment_method'   => $validated['payment_method'] ?? 'Cash on Delivery',
                'payment_status'   => 'unpaid',
                'shipping_address' => $validated['shipping_address'],
                'contact_number'   => $validated['contact_number'],
                'notes'            => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $order->items()->create($itemData);
            }

            $order->load(['user:id,name,email', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data'    => $order,
            ], 201);
        });
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = Order::with(['user:id,name,email', 'items.product', 'items.seller:id,name,email'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully.',
            'data'    => $order,
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'confirmed' && !$order->confirmed_at) {
            $updateData['confirmed_at'] = now();
        } elseif ($validated['status'] === 'shipped' && !$order->shipped_at) {
            $updateData['shipped_at'] = now();
        } elseif ($validated['status'] === 'delivered' && !$order->delivered_at) {
            $updateData['delivered_at'] = now();
            $updateData['payment_status'] = 'paid';
        }

        $order->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'data'    => $order->fresh(['user:id,name,email', 'items.product']),
        ]);
    }
}
