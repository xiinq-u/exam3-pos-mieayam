<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can manage financial categories', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $category = $this->actingAs($owner, 'sanctum')->postJson('/api/financial-categories', ['name' => 'Operasional', 'type' => 'expense'])
        ->assertCreated()->json('data');

    $this->actingAs($owner, 'sanctum')->getJson('/api/financial-categories?type=expense')
        ->assertOk()->assertJsonCount(1, 'data');
    $this->actingAs($owner, 'sanctum')->deleteJson("/api/financial-categories/{$category['id']}")->assertNoContent();
});
