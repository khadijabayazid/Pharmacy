<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::with(['category', 'details'])
            ->when(
                $request->filled('category_id'),
                function ($query) use ($request) {
                    $query->where('category_id', $request->category_id);
                }
            )
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products,
        ]);
    }

    public function search(SearchProductRequest $request)
    {
        $products = Product::with('category', 'details')
            ->where('name', 'like', '%' . $request->q . '%')
            ->limit(20)
            ->get();


        return response()->json([
            'status' => true,
            'data'   => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $details = $data['details'] ?? [];
        unset($data['details']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        foreach ($details as $detail) {
            $product->details()->create($detail);
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم إنشاء المنتج بنجاح',
            'data'    => $product->load('details'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'details']);

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $details = $data['details'] ?? null;
        unset($data['details']);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        if ($details !== null) {
            $product->details()->delete();
            foreach ($details as $detail) {
                $product->details()->create($detail);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث المنتج بنجاح',
            'data'    => $product->load('details'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف المنتج بنجاح',
        ]);
    }
}
