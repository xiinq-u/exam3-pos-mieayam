<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can list all employees except themselves', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    User::factory()->create(['role' => 'cashier', 'name' => 'Kasir 1']);
    User::factory()->create(['role' => 'kitchen', 'name' => 'Dapur 1']);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/users');

    $response->assertOk();
    $users = $response->json('data');
    $this->assertCount(2, $users);
    $this->assertTrue(in_array('Kasir 1', array_column($users, 'name')));
    $this->assertTrue(in_array('Dapur 1', array_column($users, 'name')));
});

test('owner can create a new cashier employee', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $response = $this->actingAs($owner, 'sanctum')->postJson('/api/users', [
        'name' => 'Budi Kasir',
        'email' => 'budi@example.com',
        'password' => 'Password123!',
        'role' => 'cashier',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('users', [
        'name' => 'Budi Kasir',
        'email' => 'budi@example.com',
        'role' => 'cashier',
    ]);
});

test('owner can create a new kitchen employee', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $response = $this->actingAs($owner, 'sanctum')->postJson('/api/users', [
        'name' => 'Ani Dapur',
        'email' => 'ani@example.com',
        'password' => 'Password123!',
        'role' => 'kitchen',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('users', [
        'name' => 'Ani Dapur',
        'email' => 'ani@example.com',
        'role' => 'kitchen',
    ]);
});

test('cashier cannot create employees', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = $this->actingAs($cashier, 'sanctum')->postJson('/api/users', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'Password123!',
        'role' => 'cashier',
    ]);

    $response->assertForbidden();
});

test('owner can update employee role', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = $this->actingAs($owner, 'sanctum')->patchJson("/api/users/{$cashier->id}", [
        'role' => 'kitchen',
    ]);

    $response->assertOk();
    $this->assertSame('kitchen', $cashier->fresh()->role);
});

test('owner cannot update their own role', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $response = $this->actingAs($owner, 'sanctum')->patchJson("/api/users/{$owner->id}", [
        'role' => 'cashier',
    ]);

    $response->assertUnprocessable();
});

test('owner can reset employee password', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = $this->actingAs($owner, 'sanctum')->postJson("/api/users/{$cashier->id}/reset-password", [
        'password' => 'NewPassword123!',
    ]);

    $response->assertOk();
});
