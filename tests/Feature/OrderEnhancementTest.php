<?php

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cashier checkout assigns a queue number and preserves the order note', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['category_id' => Category::factory()]);

    $this->actingAs($cashier, 'sanctum')->postJson('/api/cashier/add', ['product_id' => $product->id, 'quantity' => 1]);
    $response = $this->actingAs($cashier, 'sanctum')->postJson('/api/cashier/checkout', [
        'customer_name' => 'Ani',
        'order_type' => 'dine_in',
        'order_note' => 'Tanpa sawi',
    ]);

    $response->assertCreated()->assertJsonPath('order.queue_number', 1)->assertJsonPath('order.order_note', 'Tanpa sawi');
});

test('cashier can checkout with items sent directly to the api', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $product = Product::factory()->create(['category_id' => Category::factory()]);

    $this->actingAs($cashier, 'sanctum')->postJson('/api/cashier/checkout', [
        'customer_name' => 'Sari',
        'order_type' => 'take_away',
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ])->assertCreated()->assertJsonPath('order.items.0.quantity', 2);
});

test('cashier can pay an unpaid order through the API', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $order = Order::factory()->create(['payment_status' => 'unpaid', 'total' => 15000]);

    $this->actingAs($cashier, 'sanctum')->postJson("/api/orders/{$order->id}/pay", [
        'payment_method' => 'cash',
        'paid_amount' => 20000,
    ])->assertOk()->assertJsonPath('data.payment_status', 'paid')->assertJsonPath('data.change_amount', '5000.00');
});

test('cashier can list pending unpaid orders', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    Order::factory()->create(['payment_status' => 'unpaid', 'status' => 'pending']);
    Order::factory()->create(['payment_status' => 'paid', 'status' => 'pending']);

    $this->actingAs($cashier, 'sanctum')
        ->getJson('/api/cashier/orders/pending')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.payment_status', 'unpaid');
});

test('cashier can view an order detail', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $order = Order::factory()->create(['user_id' => $cashier->id]);

    $this->actingAs($cashier, 'sanctum')
        ->getJson("/api/cashier/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $order->id)
        ->assertJsonPath('data.user.id', $cashier->id);
});

test('cashier can edit their pending unpaid order', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $order = Order::factory()->create(['user_id' => $cashier->id, 'status' => 'pending', 'payment_status' => 'unpaid']);

    $this->actingAs($cashier, 'sanctum')->patchJson("/api/orders/{$order->id}", ['order_note' => 'Tanpa sawi'])
        ->assertOk()->assertJsonPath('data.order_note', 'Tanpa sawi');
});

test('order refund is captured in the audit log', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $order = Order::factory()->create(['payment_status' => 'paid', 'paid_amount' => 20000, 'total' => 20000]);

    $this->actingAs($owner, 'sanctum')->postJson("/api/orders/{$order->id}/refund", [
        'amount' => 10000,
        'reason' => 'Produk tidak tersedia',
    ])->assertOk();

    $this->assertDatabaseHas('audit_logs', ['action' => 'order.refunded', 'auditable_id' => $order->id]);
    expect(AuditLog::count())->toBe(1);
});

test('owner can list orders by status for order management', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    Order::factory()->create(['status' => 'pending']);
    Order::factory()->create(['status' => 'completed']);

    $this->actingAs($owner, 'sanctum')->getJson('/api/orders?status=pending')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
