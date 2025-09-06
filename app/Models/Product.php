<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
use HasFactory;
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

    public static function scopeActive($query){
        return $query->where('is_active',true);
    }
}
