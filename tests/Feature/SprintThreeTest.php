<?php

use App\Models\Material;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('kitchen staff can see orders filtered by status', function () {
    $kitchen = User::factory()->create(['role' => 'kitchen']);

    Order::factory()->create(['status' => 'pending']);
    Order::factory()->create(['status' => 'processed']);
    Order::factory()->create(['status' => 'ready']);
    Order::factory()->create(['status' => 'completed']);

    $response = $this->actingAs($kitchen, 'sanctum')->getJson('/api/kitchen/orders?status=pending,processed');

    $response->assertOk();
    $this->assertCount(2, $response->json('data'));
});

test('kitchen staff cannot update order status if not kitchen role', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($cashier, 'sanctum')->postJson("/api/orders/{$order->id}/status", [
        'status' => 'processed',
        'note' => 'Pesanan sedang dimasak',
    ]);

    $response->assertForbidden();
});

test('kitchen staff can update order from pending to processed', function () {
    $kitchen = User::factory()->create(['role' => 'kitchen']);
    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($kitchen, 'sanctum')->postJson("/api/orders/{$order->id}/status", [
        'status' => 'processed',
        'note' => 'Pesanan sedang dimasak',
    ]);

    $response->assertOk();
    $this->assertSame('processed', $order->fresh()->status);
});

test('low stock alert returns materials below minimum threshold', function () {
    $kitchen = User::factory()->create(['role' => 'kitchen']);

    Material::factory()->create(['name' => 'Mie', 'stock' => 5, 'minimum_stock' => 10]);
    Material::factory()->create(['name' => 'Ayam', 'stock' => 2, 'minimum_stock' => 3]);
    Material::factory()->create(['name' => 'Garam', 'stock' => 50, 'minimum_stock' => 10]);

    $response = $this->actingAs($kitchen, 'sanctum')->getJson('/api/materials/low-stock');

    $response->assertOk();
    $materials = $response->json('data');
    $this->assertCount(2, $materials);
    $this->assertTrue(in_array('Mie', array_column($materials, 'name')));
    $this->assertTrue(in_array('Ayam', array_column($materials, 'name')));
});

test('kitchen can view recent stock movements', function () {
    $kitchen = User::factory()->create(['role' => 'kitchen']);
    $material = Material::factory()->create();

    $material->movements()->create([
        'user_id' => $kitchen->id,
        'type' => 'out',
        'quantity' => 5,
        'note' => 'Pemakaian untuk pesanan #1',
    ]);

    $response = $this->actingAs($kitchen, 'sanctum')->getJson("/api/materials/{$material->id}");

    $response->assertOk();
    $movements = $response->json('movements');
    $this->assertNotEmpty($movements);
    $this->assertSame('out', $movements[0]['type']);
});
