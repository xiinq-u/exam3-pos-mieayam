<?php

use App\Models\Material;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('each dashboard endpoint only accepts its matching role', function () {
    $owner = User::factory()->create(['role' => 'owner', 'is_admin' => true]);
    $cashier = User::factory()->create(['role' => 'cashier', 'is_admin' => false]);
    $kitchen = User::factory()->create(['role' => 'kitchen', 'is_admin' => false]);

    $this->actingAs($owner, 'sanctum')->getJson('/api/owner/dashboard')->assertOk();
    $this->actingAs($cashier, 'sanctum')->getJson('/api/cashier/dashboard')->assertOk();
    $this->actingAs($kitchen, 'sanctum')->getJson('/api/kitchen/dashboard')->assertOk();

    $this->actingAs($cashier, 'sanctum')->getJson('/api/owner/dashboard')->assertForbidden();
    $this->actingAs($kitchen, 'sanctum')->getJson('/api/reports/profit-loss?start_date=2026-09-01&end_date=2026-09-02')->assertForbidden();
    $this->actingAs($owner, 'sanctum')->getJson('/api/cashier/dashboard')->assertForbidden();
});

test('owner dashboard returns business and complete inventory summaries', function () {
    $owner = User::factory()->create(['role' => 'owner', 'is_admin' => true]);
    Product::factory()->create(['is_available' => true]);
    Material::factory()->create(['name' => 'Aman', 'stock' => 20, 'minimum_stock' => 5, 'purchase_price' => 1000]);
    Material::factory()->create(['name' => 'Menipis', 'stock' => 5, 'minimum_stock' => 5, 'purchase_price' => 2000]);
    Material::factory()->create(['name' => 'Habis', 'stock' => 0, 'minimum_stock' => 5, 'purchase_price' => 3000]);
    Order::factory()->create(['status' => 'completed', 'payment_status' => 'paid', 'total' => 25000]);

    $this->actingAs($owner, 'sanctum')->getJson('/api/owner/dashboard')
        ->assertOk()
        ->assertJsonPath('today_sales', 25000)
        ->assertJsonPath('today_orders', 1)
        ->assertJsonPath('active_products', 1)
        ->assertJsonPath('safe_materials', 1)
        ->assertJsonPath('low_stock_materials', 1)
        ->assertJsonPath('out_of_stock_materials', 1)
        ->assertJsonPath('inventory_value', 30000)
        ->assertJsonCount(3, 'materials')
        ->assertJsonCount(2, 'low_stock_list')
        ->assertJsonCount(7, 'revenue_chart');
});

test('cashier dashboard prioritizes completed orders that remain unpaid', function () {
    Carbon::setTestNow('2026-09-03 12:00:00');
    $cashier = User::factory()->create(['role' => 'cashier', 'is_admin' => false]);
    $pendingOrder = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'created_at' => now()->subMinutes(20),
    ]);
    $completedOrder = Order::factory()->create([
        'status' => 'completed',
        'payment_status' => 'unpaid',
        'created_at' => now()->subMinutes(5),
    ]);

    $this->actingAs($cashier, 'sanctum')->getJson('/api/cashier/dashboard')
        ->assertOk()
        ->assertJsonPath('unpaid_orders_count', 2)
        ->assertJsonPath('unpaid_orders.0.id', $completedOrder->id)
        ->assertJsonPath('unpaid_orders.1.id', $pendingOrder->id);

    Carbon::setTestNow();
});

test('kitchen dashboard exposes queues and stock without financial values', function () {
    $kitchen = User::factory()->create(['role' => 'kitchen', 'is_admin' => false]);
    Order::factory()->create(['status' => 'pending', 'payment_status' => 'unpaid']);
    Material::factory()->create(['stock' => 0, 'minimum_stock' => 5, 'purchase_price' => 12000]);

    $this->actingAs($kitchen, 'sanctum')->getJson('/api/kitchen/dashboard')
        ->assertOk()
        ->assertJsonPath('new_orders_count', 1)
        ->assertJsonPath('out_of_stock_count', 1)
        ->assertJsonCount(1, 'new_orders')
        ->assertJsonMissing(['purchase_price' => '12000.00']);

    $this->actingAs($kitchen, 'sanctum')->getJson('/api/materials')
        ->assertOk()
        ->assertJsonMissing(['purchase_price' => '12000.00']);
});

test('kitchen completing an order does not mark its payment as paid', function () {
    $kitchen = User::factory()->create(['role' => 'kitchen', 'is_admin' => false]);
    $order = Order::factory()->create(['status' => 'pending', 'payment_status' => 'unpaid']);

    foreach (['processed', 'ready', 'completed'] as $status) {
        $this->actingAs($kitchen, 'sanctum')->postJson("/api/orders/{$order->id}/status", ['status' => $status])->assertOk();
    }

    expect($order->fresh()->status)->toBe('completed')
        ->and($order->fresh()->payment_status)->toBe('unpaid');
});
