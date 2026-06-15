<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

test('cashier can view products', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('cashier.index'));

    $response->assertStatus(200);
    $response->assertViewIs('cashier.index');
})->group('cashier');

test('cashier can add product to cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $response = $this->actingAs($user)->post(route('cashier.add'), [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response->assertRedirect(route('cashier.index'));
    $this->assertNotEmpty(session('cart'));
    $this->assertEquals(2, session('cart')[0]['quantity']);
})->group('cashier');

test('cashier can checkout and create pending order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50000]);

    // Add to cart
    $this->actingAs($user)->post(route('cashier.add'), [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    // Checkout
    $response = $this->post(route('cashier.checkout'), [
        'customer_name' => 'Budi',
        'order_type' => 'dine_in',
    ]);

    // Verify order created with pending status
    $order = Order::latest()->first();
    $response->assertRedirect(route('orders.show', $order));

    $this->assertEquals('Budi', $order->customer_name);
    $this->assertEquals('pending', $order->status);
    $this->assertEquals(50000, $order->total);
    $this->assertNull($order->paid_amount);
})->group('cashier');

test('cashier can view pending orders', function () {
    $user = User::factory()->create();

    Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 100000,
    ]);

    $response = $this->actingAs($user)->get(route('orders.pending'));

    $response->assertStatus(200);
    $response->assertViewIs('orders.pending');
})->group('cashier');

test('cashier can complete order with payment', function () {
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 50000,
    ]);

    $response = $this->actingAs($user)->post(route('orders.complete', $order), [
        'payment_method' => 'cash',
        'paid_amount' => 50000,
    ]);

    $response->assertRedirect(route('orders.pending'));

    // Verify order marked as completed
    $order->refresh();
    $this->assertEquals('completed', $order->status);
    $this->assertEquals(50000, $order->paid_amount);
    $this->assertEquals(0, $order->change_amount);
})->group('cashier');

test('payment change calculated correctly', function () {
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 50000,
    ]);

    $this->actingAs($user)->post(route('orders.complete', $order), [
        'payment_method' => 'cash',
        'paid_amount' => 60000,
    ]);

    $order->refresh();
    $this->assertEquals(60000, $order->paid_amount);
    $this->assertEquals(10000, $order->change_amount);
})->group('cashier');
