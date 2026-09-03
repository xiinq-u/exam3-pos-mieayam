<?php

use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can login and receive api token', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'owner@example.com',
        'password' => bcrypt('password123'),
        'role' => 'owner',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'owner@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'user' => ['id', 'name', 'email', 'role'],
        'token',
    ]);
    $this->assertSame('owner', $response->json('user.role'));
    $this->assertDatabaseHas('audit_logs', ['user_id' => $user->id, 'action' => 'auth.login']);
});

test('authorized user can create material stock entry', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'role' => 'owner',
    ]);

    $response = $this->actingAs($user)->postJson('/api/materials', [
        'name' => 'Mie',
        'sku' => 'MIE-001',
        'unit' => 'pack',
        'initial_stock' => 20,
        'minimum_stock' => 5,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('materials', [
        'name' => 'Mie',
        'sku' => 'MIE-001',
        'stock' => 20,
        'minimum_stock' => 5,
    ]);
});

test('owner can adjust stock using movement record', function () {
    /** @var User $user */
    $user = User::factory()->create(['role' => 'owner']);

    /** @var Material $material */
    $material = Material::factory()->create([
        'stock' => 10,
        'minimum_stock' => 3,
    ]);

    $response = $this->actingAs($user)->postJson("/api/materials/{$material->id}/adjust", [
        'type' => 'out',
        'quantity' => 3,
        'note' => 'Pemakaian mie untuk order',
    ]);

    $response->assertOk();
    $material->refresh();
    $this->assertSame(7, $material->stock);
    $this->assertDatabaseHas('material_movements', [
        'material_id' => $material->id,
        'type' => 'out',
        'quantity' => 3,
    ]);
});
