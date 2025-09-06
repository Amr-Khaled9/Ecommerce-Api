<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    public function index()
    {
        $products =Product::all();
        return response()->json([
            'status' => true,
            'message' => 'Products fetched successfully',
            'data' => $products
        ], 200);    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            'name'=> $request->name,
            'slug'=> Str::slug($request->name,'-'),
            'description'=> $request->description,
            'price'=> $request->price,
            'stock'=> $request->stock,
            'sku'=> $request->sku,
            'is_active'=> $request->is_active,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);


    }


    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json([
            'status' => true,
            'message' => 'Product fetched successfully',
            'data' => $product
        ], 200);
    }


    public function update(UpdateProductRequest $request)
    {
        $product = Product::findOrFail($request->id);
        if($request->has('name')){
             $product->name= $request->name;
        $product->slug= Str::slug($request->name,'-');
    }
        if($request->has('description')) $request->description = $request->description;
        if($request->has('price')) $request->price = $request->price;
        if($request->has('stock')) $request->stock = $request->stock;
        if($request->has('sku')) $request->sku = $request->sku;
        if($request->has('is_active')) $request->is_active = $request->is_active;

        $product->save();
        return response()->json([
            'status' => true,
            'message' => 'Product update successfully',
            'data' => $product
        ], 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json([
            'status' => true,
            'message' => 'Product delete successfully',
         ], 200);

    }

    // search

}
