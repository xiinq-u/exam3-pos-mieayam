<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Kolom pesanan yang boleh disimpan saat kasir membuat atau menyelesaikan order.
     */
    protected $fillable = [
        'order_number',
        'customer_name',
        'user_id',
        'total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'order_type',
        'status',
        'barcode_reference',
    ];

    /**
     * Mengatur angka uang agar selalu dibaca sebagai nilai desimal.
     */
    protected $casts = [
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    /**
     * Satu pesanan punya banyak item/menu yang dibeli.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Pesanan dibuat oleh satu user/kasir.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
