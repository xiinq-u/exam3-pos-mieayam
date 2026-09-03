<?php

use App\Models\Material;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('order status can move through workflow and create history', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($owner, 'sanctum')->postJson("/api/orders/{$order->id}/status", [
        'status' => 'processed',
        'note' => 'Pesanan sedang dimasak',
    ]);

    $response->assertOk();
    $this->assertSame('processed', $order->fresh()->status);
    $this->assertDatabaseHas('order_status_histories', [
        'order_id' => $order->id,
        'from_status' => 'pending',
        'to_status' => 'processed',
    ]);
});

test('completed order consumes material stock based on recipe', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $mie = Material::factory()->create(['name' => 'Mie', 'sku' => 'MIE-001', 'stock' => 20]);
    $ayam = Material::factory()->create(['name' => 'Ayam', 'sku' => 'AYAM-001', 'stock' => 20]);

    $product = Product::factory()->create(['name' => 'Mie Ayam']);
    $product->productMaterials()->createMany([
        ['material_id' => $mie->id, 'quantity_per_unit' => 1],
        ['material_id' => $ayam->id, 'quantity_per_unit' => 2],
    ]);

    $order = Order::factory()->create(['status' => 'pending']);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => 20000,
        'quantity' => 3,
        'subtotal' => 60000,
    ]);

    $response = $this->actingAs($owner, 'sanctum')->postJson("/api/orders/{$order->id}/status", [
        'status' => 'completed',
        'note' => 'Pesanan selesai',
    ]);

    $response->assertOk();
    $mie->refresh();
    $ayam->refresh();

    $this->assertSame(17, $mie->stock);
    $this->assertSame(14, $ayam->stock);
});
