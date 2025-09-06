<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable =[
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'sku',
        'is_active',
    ];
    // in stock

    public  function isStock()
    {
        return $this->stock >0;
    }
}
