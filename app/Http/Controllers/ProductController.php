<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product = Product::query()
            ->select(['id', 'category_id', 'name', 'description', 'price', 'stock'])
            ->with(['category:id,name', 'mainImage:id,product_id,image_path', 'images:id,product_id,image_path,order'])
            ->get();

        return response()->json($product);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(([
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|string',
            'slug' => 'required|string|unique:products,slug',
            'description' => 'required|string',
            'price' => 'required|decimal:0,2|min:0',
            'stock' => 'required|integer',
            'is_active' => 'nullable|boolean'
        ]));

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product muvaffaqiyatli yaratildi',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate(([
            'category_id' => 'sometimes|required|integer|exists:categories,id',
            'name' => 'sometimes|required|string',
            'slug' => 'sometimes|required|string|unique:products,slug,' . $product->id,
            'description' => 'sometimes|string',
            'price' => 'sometimes|required|decimal:0,2|min:0',
            'stock' => 'sometimes|required|integer',
            'is_active' => 'nullable|boolean'
        ]));

        $product->update($validated);

        return response()->json([
            'message' => 'Product yangilandi',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'o\'chirildi'
        ]);
    }
}
