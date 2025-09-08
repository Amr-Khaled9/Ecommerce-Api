<?php

namespace App\Repository;

use App\Models\Category;
use App\Repository\RepositoryInterface\CategoryRepositoryInterface;
use Illuminate\Support\Str;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function store( $request){
        $slug = Str::slug($request->name,'-');
        return $category= Category::create([
            'name'=>$request->name,
            'slug'=>$slug,
            'description'=>$request->description,
            'is_active'=>$request->is_active,
            'parent_id'=>$request->parent_id,
        ]);

    }
    public function update( $request ,$id){
        $category = Category::findOrFail($id);
        if($request->has('name')){
            $category->name= $request->name;
            $category->slug= Str::slug($request->name,'-');
        }
        if($request->has('description')) $category->description = $request->description;
        if($request->has('is_active')) $category->is_active = $request->is_active;
        if($request->has('parent_id')) $category->parent_id = $request->parent_id;
         $category->save();
        return $category;
    }
}
