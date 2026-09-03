<?php

namespace App\Models;

use Database\Factories\MaterialMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialMovement extends Model
{
    /** @use HasFactory<MaterialMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'material_id',
        'user_id',
        'type',
        'loss_reason',
        'quantity',
        'note',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
