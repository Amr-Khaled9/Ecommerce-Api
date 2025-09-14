<?php


namespace App\Repository;

use App\Models\Product;
use App\Repository\RepositoryInterface\ProductRepositoryInterface;
use Illuminate\Support\Str;

class ProductRepository implements ProductRepositoryInterface
{
    public function store($request)
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($request->name, '-');
        if($request->hasFile($data['image'])){
            $data['image']= $request->file('image')->storeAs('products',$data['slug'] ,'public');
        }
        $product = Product::create($data);
        if ($request->has($data['categories'])) {
            $product->categories()->attach($data['categories']);
        }
        $product->load('categories');
        return $product;
    }

    public function update($request, $id)
    {
        $product = Product::findOrFail($id);
        if ($request->has('name')) {
            $product->name = $request->name;
            $product->slug = Str::slug($request->name, '-');
        }
        if ($request->has('description')) $product->description = $request->description;
        if ($request->has('price')) $product->price = $request->price;
        if ($request->has('stock')) $product->stock = $request->stock;
        if ($request->has('sku')) $product->sku = $request->sku;
        if ($request->has('is_active')) $product->is_active = $request->is_active;
        if($request->hasFile($request->image)){
            $request->image = $request->file('image')->storeAs('products',$product->slug ,'public');
        }
        $product->save();

        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }
        $product->load('categories');
        return $product;
    }

    public function filterByPrice($request)
    {
        return Product::query()
            ->when($request->price_min, fn($q) => $q->where('price', '>=', $request->price_min))
            ->when($request->price_max, fn($q) => $q->where('price', '<=', $request->price_max))
            ->get();

    }

}

