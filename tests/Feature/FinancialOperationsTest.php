<?php

use App\Models\CashierShift;
use App\Models\Expense;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can record and cancel an expense without deleting its history', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $expense = $this->actingAs($owner, 'sanctum')->postJson('/api/expenses', [
        'category' => 'Operasional',
        'description' => 'Beli gas',
        'amount' => 25000,
        'expense_date' => now()->toDateString(),
    ])->assertCreated()->json('data');

    $this->assertDatabaseHas('audit_logs', ['action' => 'expense.created', 'auditable_id' => $expense['id']]);

    $this->actingAs($owner, 'sanctum')->postJson("/api/expenses/{$expense['id']}/cancel", [
        'reason' => 'Input ganda',
    ])->assertOk();

    expect(Expense::find($expense['id'])->cancelled_at)->not->toBeNull();
});

test('cashier can open and close a shift with cash difference', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $shift = $this->actingAs($cashier, 'sanctum')->postJson('/api/shifts/open', [
        'opening_cash' => 100000,
    ])->assertCreated()->json('data');

    Order::factory()->create([
        'user_id' => $cashier->id,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'total' => 50000,
        'created_at' => now(),
    ]);

    $this->actingAs($cashier, 'sanctum')->postJson("/api/shifts/{$shift['id']}/close", [
        'actual_cash' => 149000,
        'closing_note' => 'Selisih seribu rupiah',
    ])->assertOk();

    expect(CashierShift::find($shift['id'])->cash_difference)->toEqual('-1000.00');
});

test('profit loss excludes cancelled expenses and subtracts refunds', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    Order::factory()->create([
        'status' => 'completed',
        'payment_status' => 'paid',
        'total' => 100000,
        'refund_amount' => 10000,
        'refunded_at' => now(),
        'created_at' => now(),
    ]);
    Expense::factory()->create(['amount' => 30000, 'expense_date' => now()]);
    Expense::factory()->create(['amount' => 50000, 'expense_date' => now(), 'cancelled_at' => now()]);

    $this->actingAs($owner, 'sanctum')->getJson('/api/reports/profit-loss?start_date='.now()->toDateString().'&end_date='.now()->toDateString())
        ->assertOk()
        ->assertJsonPath('net_profit', 60000);
});

test('owner can refund a paid order but cannot cancel a completed order', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $order = Order::factory()->create([
        'status' => 'completed',
        'payment_status' => 'paid',
        'paid_amount' => 50000,
        'total' => 50000,
    ]);

    $this->actingAs($owner, 'sanctum')->postJson("/api/orders/{$order->id}/cancel", [
        'reason' => 'Pelanggan berubah pikiran',
    ])->assertUnprocessable();

    $this->actingAs($owner, 'sanctum')->postJson("/api/orders/{$order->id}/refund", [
        'amount' => 20000,
        'reason' => 'Menu tidak tersedia',
    ])->assertOk()->assertJsonPath('order.payment_status', 'partial_refund');
});
