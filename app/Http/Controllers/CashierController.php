<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CashierController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('is_available', true)->with('category')->orderBy('name')->get();
        $cart = Session::get('cart', []);
        $items = collect($cart)->map(function ($item, $productId) {
            $product = Product::find($productId);

            return $product ? array_merge($item, [
                'product' => $product,
                'subtotal' => $product->price * $item['quantity'],
            ]) : null;
        })->filter();

        $total = $items->sum('subtotal');

        return view('cashier.index', [
            'products' => $products,
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function addToCart(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Session::get('cart', []);
        $productId = (string) $data['product_id'];

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $data['quantity'];
        } else {
            $cart[$productId] = ['quantity' => $data['quantity']];
        }

        Session::put('cart', $cart);

        return redirect()->route('cashier.index')->with('success', 'Menu added to cart.');
    }

    public function removeFromCart(Request $request, Product $product)
    {
        $cart = Session::get('cart', []);
        $productId = (string) $product->id;

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
        }

        return redirect()->route('cashier.index')->with('success', 'Item removed from cart.');
    }

    public function clearCart(): RedirectResponse
    {
        Session::forget('cart');

        return redirect()->route('cashier.index')->with('success', 'Cart cleared.');
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'order_type' => 'required|in:dine_in,take_away',
        ]);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cashier.index')->with('error', 'Cart is empty.');
        }

        $items = collect($cart)->map(function ($item, $productId) {
            $product = Product::find($productId);
            if (! $product) {
                return null;
            }

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'subtotal' => $product->price * $item['quantity'],
            ];
        })->filter();

        if ($items->isEmpty()) {
            return redirect()->route('cashier.index')->with('error', 'Cart contains invalid items.');
        }

        $total = $items->sum('subtotal');

        $order = Order::create([
            'order_number' => 'ORD-'.now()->format('YmdHis').'-'.rand(100, 999),
            'user_id' => $request->user()->id,
            'total' => $total,
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'order_type' => $data['order_type'],
            'status' => 'pending',
        ]);

        foreach ($items as $item) {
            OrderItem::create(array_merge($item, ['order_id' => $order->id]));
        }

        Session::forget('cart');

        return redirect()->route('cashier.index')->with('success', 'Pesanan berhasil dibuat. Pesanan akan segera diproses.');
    }

    public function pendingOrders()
    {
        $orders = Order::where('status', 'pending')->latest()->paginate(15);

        return view('orders.pending', compact('orders'));
    }

    public function showOrder(Order $order)
    {
        $order->load('items', 'user');

        return view('orders.show', compact('order'));
    }

    public function completeOrder(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_method' => 'required|in:cash,qris',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        if ($data['payment_method'] === 'cash' && $data['paid_amount'] < $order->total) {
            return back()->withInput()->with('error', 'Jumlah pembayaran tidak cukup.');
        }

        $order->update([
            'payment_method' => $data['payment_method'],
            'paid_amount' => $data['paid_amount'],
            'change_amount' => max(0, $data['paid_amount'] - $order->total),
            'status' => 'completed',
        ]);

        return redirect()->route('orders.pending')->with('success', 'Pesanan selesai dan pembayaran diterima.');
    }
}
