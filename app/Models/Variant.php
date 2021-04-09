<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];

    // protected $with = ['variations'];

    public function variations()
    {
        return $this->hasMany(ProductVariant::class, 'variant_id')->groupBy('variant');
    }

}
