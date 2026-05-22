<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::query()
            ->with(['products', 'products.mainImage:id,product_id,image_path'])
            ->get();
        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'total_price' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1'
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'total_price' => $validated['total_price'],
                    'note' => $validated['note'] ?? null,
                    'status' => 'pending'
                ]);

                $pivotData = [];
                foreach ($validated['products'] as $item) {
                    $product = Product::lockForUpdate()->find($item['id']);

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("{$product->name} mahsulotidan yetarli miqdorda mavjud emas!");
                    }

                    $pivotData[$item['id']] = [
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price
                    ];

                    $product->decrement('stock', $item['quantity']);
                }

                $order->products()->attach($pivotData);
                $order->load('products');

                return response()->json([
                    'message' => 'Buyurtma muvaffaqiyatli qabul qilindi',
                    'data' => $order
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Xatolik yuz berdi',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        if (Auth::id() !== $order->user_id) {
            return response()->json([
                'error' => 'Siz bu buyurtmani o\'zgartira olmaysiz!'
            ], 403);
        }
        $validated = $request->validate([
            'total_price' => 'sometimes|required|numeric',
            'note' => 'nullable|string'
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'Order yangilandi',
            'data' => $order
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
