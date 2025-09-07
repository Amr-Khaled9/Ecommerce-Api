<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Repository\RepositoryInterface\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private $category ;

    public function __construct(CategoryRepositoryInterface $category)
    {
        $this->category =$category;
    }

    public function index()
    {
        $categories =Category::all();
        return response()->json([
            'status' => true,
            'message' => 'Categories fetched successfully',
            'data' => $categories
        ], 200);
    }

    public function store(CategoryStoreRequest $request)
    {
        $category =$this->category->store($request);
        return response()->json([
            'status' => true,
            'message' => 'Category store successfully',
            'data' => $category
        ], 200);
    }

    public function show($id)
    {
        $category = Category::where('id', $id)->firstOrFail();
        $category->load(['parent', 'children']);
        return response()->json([
            'status' => true,
            'message' => 'Category retrieved successfully',
            'data' => $category
        ], 200);
    }

    public function update(CategoryUpdateRequest $request,$id)
    {
        $category =$this->category->update($request,$id);
        return response()->json([
            'status' => true,
            'message' => 'Category update successfully',
            'data' => $category
        ], 200);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        foreach($category->children as $child){
            $child->parent_id = $category->parent_id;
            $child->save();
        }
        $category->delete();
        return response()->json([
            'status' => true,
            'message' => 'Category delete successfully',
         ], 200);

    }
}
