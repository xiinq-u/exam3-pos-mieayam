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
        'queue_number',
        'order_note',
        'customer_name',
        'user_id',
        'total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'order_type',
        'status',
        'payment_status',
        'barcode_reference',
        'cancelled_at',
        'cancellation_reason',
        'refund_amount',
        'refunded_at',
        'refund_reason',
    ];

    /**
     * Mengatur angka uang agar selalu dibaca sebagai nilai desimal.
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'queue_number' => 'integer',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Satu pesanan punya banyak item/menu yang dibeli.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Pesanan dibuat oleh satu user/kasir.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
