<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category', 'description', 'amount', 'income_date', 'cancelled_at', 'cancellation_reason'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'income_date' => 'date', 'cancelled_at' => 'datetime'];
    }
}
