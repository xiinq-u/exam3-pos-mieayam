<?php

use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can record other income and it contributes to profit loss', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $this->actingAs($owner, 'sanctum')->postJson('/api/incomes', [
        'category' => 'Jasa',
        'description' => 'Jual kemasan',
        'amount' => 20000,
        'payment_method' => 'transfer',
        'income_date' => now()->toDateString(),
    ])->assertCreated();

    $this->actingAs($owner, 'sanctum')->getJson('/api/incomes?start_date='.now()->toDateString().'&end_date='.now()->toDateString())
        ->assertOk()
        ->assertJsonPath('data.0.payment_method', 'transfer')
        ->assertJsonPath('data.0.user.name', $owner->name);

    $this->actingAs($owner, 'sanctum')->getJson('/api/reports/profit-loss?start_date='.now()->toDateString().'&end_date='.now()->toDateString())
        ->assertOk()
        ->assertJsonPath('other_income', 20000)
        ->assertJsonPath('net_profit', 20000);
});

test('owner sees gross profit before other income and expenses in profit loss', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    Income::factory()->create(['amount' => 15000, 'income_date' => now()]);
    Expense::factory()->create(['amount' => 5000, 'expense_date' => now()]);

    $this->actingAs($owner, 'sanctum')->getJson('/api/reports/profit-loss?start_date='.now()->toDateString().'&end_date='.now()->toDateString())
        ->assertOk()
        ->assertJsonPath('gross_profit', 0)
        ->assertJsonPath('net_profit', 10000);
});
