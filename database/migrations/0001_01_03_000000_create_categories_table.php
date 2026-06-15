<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel kategori menu, misalnya Mie Ayam, Minuman, dan Topping.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel kategori jika migration dibatalkan.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
