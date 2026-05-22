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
            ->select(['id', 'name', 'slug', 'image', 'is_active'])
            ->latest()
            ->paginate(15);

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug|max:255',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean'
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Categoriya muvaffaqiyatli yaratildi',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:categories,slug,' . $category->id,
            'image' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|required|boolean'
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
