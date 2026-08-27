<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Product;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }

    public function stats()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'total_categories' => Category::count(),
                'total_products' => Product::count(),
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء التصنيف بنجاح',
            'data' => $category
        ], 201);
    }

    public function show(Category $category)
    {
        $category->load('products');

        return response()->json([
            'status' => true,
            'data' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث التصنيف بنجاح',
            'data' => $category,
        ]);
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكن حذف تصنيف مرتبط بمنتجات',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف التصنيف بنجاح',
        ]);
    }
}