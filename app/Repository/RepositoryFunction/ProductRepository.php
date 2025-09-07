<?php


namespace App\Repository\RepositoryFunction;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Repository\RepositoryInterface\ProductRepositoryInterface;
use Illuminate\Support\Str;

class ProductRepository implements ProductRepositoryInterface
{
    public function store($request){
        $data = $request->validated();

        $data['slug'] = Str::slug($request->name, '-');

         return $product = Product::create($data);
    }
    public function update($request ,$id){
        $product = Product::findOrFail($id);
        if($request->has('name')){
            $product->name= $request->name;
            $product->slug= Str::slug($request->name,'-');
        }
        if($request->has('description')) $product->description = $request->description;
        if($request->has('price')) $product->price = $request->price;
        if($request->has('stock')) $product->stock = $request->stock;
        if($request->has('sku')) $product->sku = $request->sku;
        if($request->has('is_active')) $product->is_active = $request->is_active;

         return $product->save();
    }

    public function filterByPrice($request)
    {
        return Product::query()
            ->when($request->price_min,fn($q)=>$q->where('price','>=',$request->price_min))
            ->when($request->price_max,fn($q)=>$q->where('price','<=',$request->price_max))
            ->get();

    }

}

