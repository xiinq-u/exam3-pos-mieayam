<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-'.now()->format('YmdHis').'-'.fake()->unique()->numberBetween(100, 999),
            'customer_name' => fake()->name(),
            'user_id' => User::factory(),
            'total' => fake()->numberBetween(10000, 200000),
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'order_type' => 'dine_in',
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ];
    }
}
