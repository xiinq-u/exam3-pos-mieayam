<?php

use App\Models\Material;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('inactive employee cannot log in', function () {
    $user = User::factory()->create([
        'role' => 'cashier',
        'is_active' => false,
        'password' => 'password123',
    ]);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertForbidden();
});

test('user can change password with current password', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $this->actingAs($user, 'sanctum')->putJson('/api/account/password', [
        'current_password' => 'password123',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertOk();
});

test('owner can update, filter and soft delete a material', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $material = Material::factory()->create(['purchase_price' => 1000]);

    $this->actingAs($owner, 'sanctum')->patchJson("/api/materials/{$material->id}", [
        'purchase_price' => 1500,
    ])->assertOk()->assertJsonPath('data.purchase_price', '1500.00');

    $this->actingAs($owner, 'sanctum')->deleteJson("/api/materials/{$material->id}")->assertNoContent();
    $this->assertSoftDeleted('materials', ['id' => $material->id]);
});

test('owner can configure a product recipe', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $product = Product::factory()->create();
    $material = Material::factory()->create(['purchase_price' => 1000]);

    $this->actingAs($owner, 'sanctum')->putJson("/api/products/{$product->id}/recipe", [
        'materials' => [['material_id' => $material->id, 'quantity_per_unit' => 2]],
    ])->assertOk()->assertJsonPath('cost_per_unit', 2000);

    $this->assertDatabaseHas('product_materials', ['product_id' => $product->id, 'material_id' => $material->id]);
});

test('owner can view the total inventory value', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    Material::factory()->create(['stock' => 10, 'purchase_price' => 2500]);

    $this->actingAs($owner, 'sanctum')->getJson('/api/reports/inventory')
        ->assertOk()
        ->assertJsonPath('total_inventory_value', 25000);
});

test('owner can record damaged material without losing the reason', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $material = Material::factory()->create(['stock' => 5]);

    $this->actingAs($owner, 'sanctum')->postJson("/api/materials/{$material->id}/adjust", [
        'type' => 'damaged',
        'quantity' => 2,
    ])->assertOk();

    $this->assertDatabaseHas('material_movements', ['material_id' => $material->id, 'type' => 'out', 'loss_reason' => 'damaged']);
});
