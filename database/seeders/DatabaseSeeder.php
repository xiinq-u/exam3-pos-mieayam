<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Menjalankan semua seeder utama.
     * Anggap ini seperti tombol untuk mengisi data awal aplikasi sekaligus.
     */
    public function run(): void
    {
        // Urutannya penting: kategori dibuat dulu, baru produk dan user admin.
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
        ]);
    }
}
