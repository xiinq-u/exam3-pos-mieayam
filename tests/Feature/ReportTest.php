<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can view sales summary by period', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    Order::factory()
        ->count(5)
        ->create(['status' => 'completed', 'total' => 50000]);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/reports/sales-summary?period=daily');

    $response->assertOk();
    $this->assertNotEmpty($response->json('data'));
});

test('owner can view product sales breakdown', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $product = Product::factory()->create(['name' => 'Mie Ayam']);
    $order = Order::factory()->create(['status' => 'completed']);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => 'Mie Ayam',
        'quantity' => 2,
        'price' => 15000,
        'subtotal' => 30000,
    ]);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/reports/product-sales');

    $response->assertOk();
    $this->assertNotEmpty($response->json('data'));
});

test('owner can view order type sales breakdown', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    Order::factory()->create(['status' => 'completed', 'order_type' => 'dine_in', 'total' => 50000]);
    Order::factory()->create(['status' => 'completed', 'order_type' => 'take_away', 'total' => 40000]);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/reports/order-type-sales');

    $response->assertOk();
    $data = $response->json('data');
    $this->assertCount(2, $data);
});

test('cashier cannot access reports', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = $this->actingAs($cashier, 'sanctum')->getJson('/api/reports/sales-summary');

    $response->assertForbidden();
});

test('owner can view order details with date filter', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    Order::factory()
        ->count(3)
        ->create(['status' => 'completed']);

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/reports/order-details');

    $response->assertOk();
    $this->assertNotEmpty($response->json('data'));
});

test('owner can export profit and loss reports as PDF and Excel', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    Order::factory()->create([
        'status' => 'completed',
        'payment_status' => 'paid',
        'total' => 50000,
    ]);

    $query = '?start_date='.now()->toDateString().'&end_date='.now()->toDateString();

    $pdf = $this->actingAs($owner, 'sanctum')->get('/api/reports/profit-loss/export/pdf'.$query);
    $pdf->assertOk()->assertDownload('laporan-laba-rugi-'.now()->toDateString().'-sampai-'.now()->toDateString().'.pdf');
    expect($pdf->headers->get('content-type'))->toContain('application/pdf');

    $excel = $this->actingAs($owner, 'sanctum')->get('/api/reports/profit-loss/export/excel'.$query);
    $excel->assertOk()->assertDownload('laporan-laba-rugi-'.now()->toDateString().'-sampai-'.now()->toDateString().'.xlsx');
});
