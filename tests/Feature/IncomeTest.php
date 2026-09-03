<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can record other income and it contributes to profit loss', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $this->actingAs($owner, 'sanctum')->postJson('/api/incomes', [
        'category' => 'Jasa',
        'description' => 'Jual kemasan',
        'amount' => 20000,
        'income_date' => now()->toDateString(),
    ])->assertCreated();

    $this->actingAs($owner, 'sanctum')->getJson('/api/reports/profit-loss?start_date='.now()->toDateString().'&end_date='.now()->toDateString())
        ->assertOk()
        ->assertJsonPath('other_income', 20000)
        ->assertJsonPath('net_profit', 20000);
});
