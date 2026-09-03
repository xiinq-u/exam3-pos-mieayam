<?php

namespace App\Models;

use Database\Factories\ProductMaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMaterial extends Model
{
    /** @use HasFactory<ProductMaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'material_id',
        'quantity_per_unit',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:2',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
