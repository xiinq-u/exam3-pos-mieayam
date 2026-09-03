<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Kolom detail item yang boleh disimpan untuk setiap menu dalam satu pesanan.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price',
        'cost_price',
        'quantity',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Satu item pembelian milik satu pesanan.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Item ini terhubung ke produk/menu asalnya.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
