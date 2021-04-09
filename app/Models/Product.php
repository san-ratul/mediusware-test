<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    protected $with = ['productVariants','productVariantPrices'];

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function productVariantPrices()
    {
        return $this->hasMany(ProductVariantPrice::class, 'product_id');
    }

}
