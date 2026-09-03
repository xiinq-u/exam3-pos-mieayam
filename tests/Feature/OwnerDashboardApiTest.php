<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can fetch dashboard summary api', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    Order::factory()->create([
        'status' => 'completed',
        'total' => 150000,
        'paid_amount' => 150000,
    ]);

    Order::factory()->create([
        'status' => 'pending',
        'total' => 50000,
    ]);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/dashboard');

    $response->assertOk()
        ->assertJsonPath('total_sales', 150000)
        ->assertJsonPath('today_sales', 150000)
        ->assertJsonPath('pending_orders', 1)
        ->assertJsonPath('completed_orders', 1)
        ->assertJsonPath('today_orders', 1);
});
