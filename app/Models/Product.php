<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Kolom menu yang boleh diisi dari form tambah/edit produk.
     */
    protected $fillable = [
        'category_id',
        'name',
        'price',
        'image',
        'is_available',
    ];

    /**
     * Mengubah tipe data agar harga dan status tersedia dibaca dengan benar oleh Laravel.
     */
    protected $casts = [
        'is_available' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Satu produk/menu masuk ke satu kategori.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
