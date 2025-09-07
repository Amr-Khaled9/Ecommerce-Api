<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\FilterByPriceRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Repository\RepositoryInterface\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private $product;
    public function __construct(ProductRepositoryInterface $product)
    {
        $this->product =$product;
    }

    public function index()
    {
        $products =Product::active()->get();
        return response()->json([
            'status' => true,
            'message' => 'Products fetched successfully',
            'data' => $products
        ], 200);
    }

    public function store(StoreProductRequest $request)
    {
         $result =$this->product->store($request);

        return response()->json([
            'status'  => true,
            'message' => 'Product created successfully',
            'data'    => $result
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
        $result =$this->product->store($request,$id);

        return response()->json([
            'status' => true,
            'message' => 'Product update successfully',
            'data' => $result
        ], 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();  // soft delete
        return response()->json([
            'status' => true,
            'message' => 'Product moved to archive successfully',
         ], 200);

    }

    public function restore(Request $request ,$id)
    {
        if($request->user()->hasRole('admin')){
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();  // soft delete
        return response()->json([
            'status' => true,
            'message' => 'Product restored successfully',
            'date' => $product,
        ], 200);
        }

        return response()->json([
            'status'=> false,
            'message'=>'You are not authorized to perform this action'
        ],403);

    }
    public function getDeleteOnly()
    {
        $products = Product::onlyTrashed()->get();
        return response()->json([
            'status' => true,
            'message' => 'Archived products fetched successfully',
            'data' => $products
        ], 200);

    }

    public function getAllProduct()
    {
        $products = Product::withTrashed()->get();
        return response()->json([
            'status' => true,
            'message' => 'All products (including archived) fetched successfully',
            'data' => $products
        ], 200);
    }

    public function forceDelete(Request $request ,$id)
    {
        if($request->user()->hasRole('admin')){
            $product = Product::findOrFail($id);
            $product->forceDelete();
            return response()->json([
                'status' => true,
                'message' => 'Product permanently deleted successfully',
                'data' => $product,
            ], 200);
        }

        return response()->json([
            'status'=> false,
            'message'=>'You are not authorized to perform this action'
        ],403);

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


    public function filterByPrice(FilterByPriceRequest $request)
    {
        $products = $this->product->filterByPrice($request);
        return response()->json([
            'status'=> true,
            'message'=>'Product retrieved successfully',
            'data'=>$products,
        ],200);
    }

}
