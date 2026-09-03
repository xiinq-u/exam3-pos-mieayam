<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cashier can view products', function () {
    $cashier = User::factory()->create(['role' => 'cashier', 'is_admin' => false]);
    Product::factory()->count(2)->create(['is_available' => true]);

    $this->actingAs($cashier, 'sanctum')->getJson('/api/cashier/products')
        ->assertOk()
        ->assertJsonCount(2);
})->group('cashier');

test('cashier can add product to cart', function () {
    $cashier = User::factory()->create(['role' => 'cashier', 'is_admin' => false]);
    $product = Product::factory()->create();

    $this->actingAs($cashier, 'sanctum')->postJson('/api/cashier/add', [
        'product_id' => $product->id,
        'quantity' => 2,
    ])->assertOk()
        ->assertJsonPath('items.0.product_id', $product->id)
        ->assertJsonPath('items.0.quantity', 2);

    expect(session('cart')[(string) $product->id]['quantity'])->toBe(2);
})->group('cashier');

test('cashier can checkout and create an unpaid pending order', function () {
    $cashier = User::factory()->create(['role' => 'cashier', 'is_admin' => false]);
    $product = Product::factory()->create(['price' => 50000]);

    $response = $this->actingAs($cashier, 'sanctum')->postJson('/api/cashier/checkout', [
        'customer_name' => 'Budi',
        'order_type' => 'dine_in',
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ]);

    $response->assertCreated()
        ->assertJsonPath('order.customer_name', 'Budi')
        ->assertJsonPath('order.status', 'pending')
        ->assertJsonPath('order.payment_status', 'unpaid')
        ->assertJsonPath('order.total', '50000.00');
})->group('cashier');

test('cashier can view unpaid orders', function () {
    $cashier = User::factory()->create(['role' => 'cashier', 'is_admin' => false]);
    Order::factory()->create(['user_id' => $cashier->id, 'status' => 'pending', 'payment_status' => 'unpaid']);
    Order::factory()->create(['user_id' => $cashier->id, 'status' => 'completed', 'payment_status' => 'paid']);

    $this->actingAs($cashier, 'sanctum')->getJson('/api/cashier/orders/pending')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.payment_status', 'unpaid');
})->group('cashier');

test('cashier payment does not change kitchen order status', function () {
    $cashier = User::factory()->create(['role' => 'cashier', 'is_admin' => false]);
    $order = Order::factory()->create([
        'user_id' => $cashier->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'total' => 50000,
    ]);

    $this->actingAs($cashier, 'sanctum')->postJson("/api/orders/{$order->id}/pay", [
        'payment_method' => 'cash',
        'paid_amount' => 50000,
    ])->assertOk()->assertJsonPath('data.payment_status', 'paid');

    expect($order->fresh()->status)->toBe('pending')
        ->and($order->fresh()->paid_amount)->toBe('50000.00')
        ->and($order->fresh()->change_amount)->toBe('0.00');
})->group('cashier');

test('payment change is calculated correctly', function () {
    $cashier = User::factory()->create(['role' => 'cashier', 'is_admin' => false]);
    $order = Order::factory()->create(['user_id' => $cashier->id, 'payment_status' => 'unpaid', 'total' => 50000]);

    $this->actingAs($cashier, 'sanctum')->postJson("/api/orders/{$order->id}/pay", [
        'payment_method' => 'cash',
        'paid_amount' => 60000,
    ])->assertOk();

    expect($order->fresh()->paid_amount)->toBe('60000.00')
        ->and($order->fresh()->change_amount)->toBe('10000.00');
})->group('cashier');
