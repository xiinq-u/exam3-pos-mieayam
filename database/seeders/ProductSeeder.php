<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $mie = Category::where('name', 'Mie Ayam')->first();
        $minuman = Category::where('name', 'Minuman')->first();
        $topping = Category::where('name', 'Topping')->first();

        if ($mie) {
            Product::create(['category_id' => $mie->id, 'name' => 'Mie Ayam Biasa', 'price' => 12000]);
            Product::create(['category_id' => $mie->id, 'name' => 'Mie Ayam Spesial', 'price' => 18000]);
        }

        if ($minuman) {
            Product::create(['category_id' => $minuman->id, 'name' => 'Es Teh', 'price' => 3000]);
            Product::create(['category_id' => $minuman->id, 'name' => 'Teh Hangat', 'price' => 3000]);
        }

        if ($topping) {
            Product::create(['category_id' => $topping->id, 'name' => 'Pangsit', 'price' => 3000]);
            Product::create(['category_id' => $topping->id, 'name' => 'Bakso', 'price' => 5000]);
        }
    }
}
