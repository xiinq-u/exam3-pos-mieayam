<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Mengisi kategori menu awal.
     * Contohnya mie ayam, minuman, dan topping.
     */
    public function run(): void
    {
        $categories = ['Mie Ayam', 'Minuman', 'Topping'];
        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
