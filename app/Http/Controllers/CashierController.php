<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CashierController extends Controller
{
    /**
     * Mengambil isi keranjang dari session dan menghitung totalnya.
     *
     * @return array{items: Collection, total: int|float}
     */
    private function cartData(): array
    {
        $cart = Session::get('cart', []);
        $items = collect($cart)->map(function ($item, $productId) {
            $product = Product::find($productId);

            return $product ? array_merge($item, [
                'product' => $product,
                'subtotal' => $product->price * $item['quantity'],
            ]) : null;
        })->filter();

        return [
            'items' => $items,
            'total' => $items->sum('subtotal'),
        ];
    }

    /**
     * Mengirim ulang tampilan tagihan setelah keranjang berubah.
     */
    private function cartResponse()
    {
        $cartData = $this->cartData();

        return response()->json([
            'html' => view('cashier.partials.cart', $cartData)->render(),
            'cart_count' => $cartData['items']->sum('quantity'),
        ]);
    }

    /**
     * Mengirim error checkout dalam format yang cocok untuk request biasa dan AJAX.
     */
    private function checkoutError(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 422);
        }

        return redirect()->route('cashier.index')->with('error', $message);
    }

    /**
     * Menampilkan halaman kasir.
     * Di sini aplikasi mengambil daftar menu yang tersedia dan isi keranjang sementara.
     */
    public function index(Request $request)
    {
        $products = Product::where('is_available', true)->with('category')->orderBy('name')->get();
        $cartData = $this->cartData();

        return view('cashier.index', [
            'products' => $products,
            'items' => $cartData['items'],
            'total' => $cartData['total'],
        ]);
    }

    /**
     * Menambahkan menu ke keranjang.
     * Keranjang disimpan sementara di session, jadi belum masuk riwayat pembelian.
     */
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

        if ($request->expectsJson()) {
            return $this->cartResponse();
        }

        return redirect()->route('cashier.index')->with('success', 'Menu added to cart.');
    }

    /**
     * Menghapus satu menu dari keranjang kasir.
     */
    public function removeFromCart(Request $request, Product $product)
    {
        $cart = Session::get('cart', []);
        $productId = (string) $product->id;

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
        }

        if ($request->expectsJson()) {
            return $this->cartResponse();
        }

        return redirect()->route('cashier.index')->with('success', 'Item removed from cart.');
    }

    /**
     * Mengosongkan semua isi keranjang.
     */
    public function clearCart(): RedirectResponse
    {
        Session::forget('cart');

        return redirect()->route('cashier.index')->with('success', 'Cart cleared.');
    }

    /**
     * Membuat pesanan dari isi keranjang.
     * Setelah pesanan dibuat, statusnya masih pending sampai kasir menyelesaikan pembayaran.
     */
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:100',
            'order_type' => 'required|in:dine_in,take_away',
        ]);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return $this->checkoutError($request, 'Cart is empty.');
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
            return $this->checkoutError($request, 'Cart contains invalid items.');
        }

        $total = $items->sum('subtotal');

        $order = Order::create([
            'order_number' => 'ORD-'.now()->format('YmdHis').'-'.rand(100, 999),
            'customer_name' => $data['customer_name'],
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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pesanan berhasil dibuat.',
                'redirect_url' => route('orders.show', $order),
            ]);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan berhasil dibuat. Pesanan akan segera diproses.');
    }

    /**
     * Menampilkan daftar pesanan yang belum dibayar atau belum selesai.
     */
    public function pendingOrders()
    {
        $orders = Order::with('items')->where('status', 'pending')->latest()->paginate(15);

        return view('orders.pending', compact('orders'));
    }

    /**
     * Menampilkan detail satu pesanan, termasuk daftar menu yang dibeli.
     */
    public function showOrder(Order $order)
    {
        $order->load('items', 'user');

        return view('orders.show', compact('order'));
    }

    /**
     * Menyelesaikan pembayaran.
     * Aplikasi menghitung uang dibayar dan kembalian, lalu mengubah status menjadi completed.
     */
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

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Pesanan selesai dan pembayaran diterima.')
            ->with('print_receipt', true);
    }
}
