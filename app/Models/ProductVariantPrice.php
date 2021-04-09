<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $fillable = ['price', 'product_variant_one', 'product_variant_two', 'product_variant_three', 'stock', 'product_id'];
    protected $with = ['vairant1','vairant2','vairant3'];
    public function vairant1()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one');
    }
    public function vairant2()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two');
    }
    public function vairant3()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three');
    }
}
