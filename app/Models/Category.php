<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
     protected $fillable =[
         'name',
         'slug',
         'description',
         'is_active',
         'parent_id'
     ];

     public function parent(): BelongsTo
     {
        return $this->belongsTo(Category::class,'parent_id');
     }

     public function children(): HasMany
     {
         return $this->hasMany(Category::class,'parent_id');

     }
     public function activeChildren()
     {
        return $this->children()->where('is_active',true);
     }

     public function isTopLevel(): bool
     {
        return is_null($this->parent_id);
     }
}
