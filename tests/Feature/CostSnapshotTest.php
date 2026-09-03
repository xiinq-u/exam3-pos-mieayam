<?php

use App\Models\Material;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('completion snapshots the recipe cost on the order item', function () {
    $kitchen = User::factory()->create(['role' => 'kitchen']);
    $material = Material::factory()->create(['stock' => 10, 'purchase_price' => 1500]);
    $product = Product::factory()->create();
    $product->productMaterials()->create(['material_id' => $material->id, 'quantity_per_unit' => 2]);
    $order = Order::factory()->create(['status' => 'ready']);
    $item = $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'price' => 10000, 'quantity' => 2, 'subtotal' => 20000]);

    $this->actingAs($kitchen, 'sanctum')->postJson("/api/orders/{$order->id}/status", ['status' => 'completed'])
        ->assertOk();

    expect($item->fresh()->cost_price)->toEqual('3000.00');
});
