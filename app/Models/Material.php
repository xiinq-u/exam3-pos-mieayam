<?php

namespace App\Models;

use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'stock',
        'minimum_stock',
        'is_active',
        'purchase_price',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'minimum_stock' => 'integer',
            'purchase_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function movements()
    {
        return $this->hasMany(MaterialMovement::class);
    }

    public function productRelations()
    {
        return $this->hasMany(ProductMaterial::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }
}
