<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CashierController extends Controller
{
    public function products(): JsonResponse
    {
        $products = Product::with('category')
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function cart(): JsonResponse
    {
        $cart = Session::get('cart', []);
        $items = collect($cart)->map(function ($item, $productId = null) {
            $product = Product::find($productId);

            if (! $product) {
                return null;
            }

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => (int) $item['quantity'],
                'subtotal' => (float) ($product->price * $item['quantity']),
            ];
        })->filter()->values();

        return response()->json([
            'items' => $items,
            'total' => (float) $items->sum('subtotal'),
        ]);
    }

    public function addToCart(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = Session::get('cart', []);
        $key = (string) $data['product_id'];

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $data['quantity'];
        } else {
            $cart[$key] = ['quantity' => $data['quantity']];
        }

        Session::put('cart', $cart);

        return $this->cart();
    }

    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'order_type' => ['required', 'in:dine_in,take_away'],
            'order_note' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ]);

        $cart = $data['items'] ?? Session::get('cart', []);

        if (empty($cart)) {
            return response()->json(['message' => 'Keranjang masih kosong.'], 422);
        }

        $items = collect($cart)->map(function ($item, $productId = null) {
            $productId = $item['product_id'] ?? $productId;
            $product = Product::find($productId);

            if (! $product) {
                return null;
            }

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => (int) $item['quantity'],
                'subtotal' => (float) ($product->price * $item['quantity']),
            ];
        })->filter()->values();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Item keranjang tidak valid.'], 422);
        }

        $total = (float) $items->sum('subtotal');

        $order = Order::create([
            'order_number' => 'ORD-'.now()->format('YmdHis').'-'.rand(100, 999),
            'queue_number' => (Order::whereDate('created_at', today())->max('queue_number') ?? 0) + 1,
            'customer_name' => $data['customer_name'],
            'order_note' => $data['order_note'] ?? null,
            'user_id' => $request->user()->id,
            'total' => $total,
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'order_type' => $data['order_type'],
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        Session::forget('cart');

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'order' => $order->load('items'),
        ], 201);
    }

    public function showOrder(Order $order): JsonResponse
    {
        return response()->json([
            'data' => $order->load(['items', 'user']),
        ]);
    }

    public function pay(Request $request, Order $order): JsonResponse
    {
        if ($order->payment_status !== 'unpaid' || $order->status === 'cancelled') {
            return response()->json(['message' => 'Pesanan tidak dapat dibayar.'], 422);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,qris'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
        ]);
        $paidAmount = $data['payment_method'] === 'qris' ? (float) $order->total : (float) ($data['paid_amount'] ?? 0);

        if ($paidAmount < (float) $order->total) {
            return response()->json(['message' => 'Jumlah pembayaran tidak cukup.'], 422);
        }

        $order->update([
            'payment_method' => $data['payment_method'],
            'paid_amount' => $paidAmount,
            'change_amount' => $paidAmount - (float) $order->total,
            'payment_status' => 'paid',
        ]);

        return response()->json(['data' => $order->fresh()]);
    }

    public function updateOrder(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== 'pending' || $order->payment_status !== 'unpaid') {
            return response()->json(['message' => 'Pesanan yang sudah diproses atau dibayar tidak dapat diubah.'], 422);
        }

        if (! $request->user()->hasRole('owner') && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak dapat mengubah pesanan kasir lain.'], 403);
        }

        $data = $request->validate([
            'customer_name' => ['sometimes', 'string', 'max:100'],
            'order_type' => ['sometimes', 'in:dine_in,take_away'],
            'order_note' => ['nullable', 'string', 'max:255'],
        ]);
        $order->update($data);

        return response()->json(['data' => $order->fresh()]);
    }
}
