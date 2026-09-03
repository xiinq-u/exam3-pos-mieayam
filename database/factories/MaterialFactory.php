<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word().' '.fake()->word(),
            'sku' => 'MAT-'.fake()->unique()->numerify('####'),
            'unit' => 'pack',
            'stock' => fake()->numberBetween(5, 50),
            'minimum_stock' => 5,
            'is_active' => true,
        ];
    }
}
