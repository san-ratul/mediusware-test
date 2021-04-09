<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['variant', 'variant_id', 'product_id'];

    protected $with = ['variantParent'];


    public function variantParent()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}
