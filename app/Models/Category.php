<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi saat membuat atau mengubah kategori.
     */
    protected $fillable = ['name'];

    /**
     * Satu kategori bisa memiliki banyak produk/menu.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
