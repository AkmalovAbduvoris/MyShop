<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::query()
            ->select(['id', 'name']);

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string',
            'image' => 'required|string',
            'description' => 'required|string',
            'is_active' => 'required|boolean'
        ]);

        $categories = Category::create($validated);

        return response()->json([
            'message' => 'Buyurtma muvaffaqiyatli yaratildi',
            'data' => $categories
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load(['id', 'name', 'slug', 'image', 'description', 'is_active']);

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string',
            'image' => 'required|string',
            'description' => 'required|string',
            'is_active' => 'required|boolean'
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category yangilandi',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'o\'chirildi'
        ]);
    }
}
