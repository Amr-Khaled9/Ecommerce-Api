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
        $products =Product::active()->get();
        return response()->json([
            'status' => true,
            'message' => 'Products fetched successfully',
            'data' => $products
        ], 200);    }

    public function store(StoreProductRequest $request)
    {
         $data = $request->validated();

         $data['slug'] = Str::slug($request->name, '-');

        // أنشئ المنتج
        $product = Product::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'Product created successfully',
            'data'    => $product
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


    public function update(UpdateProductRequest $request ,$id)
    {
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
    public function Search(Request $request)
    {
        $request->validate(['name'=>'required||string|max:255']);
        $product = Product::where('name', 'like', '%' . $request->name . '%')->get();
        return response()->json([
           'status'=> true,
            'message'=>'Product fetched successfully',
            'data'=>$product,
        ],200);

    }

}
