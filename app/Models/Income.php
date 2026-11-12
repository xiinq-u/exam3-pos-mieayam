<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category', 'description', 'amount', 'payment_method', 'income_date', 'cancelled_at', 'cancellation_reason'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'income_date' => 'date', 'cancelled_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
