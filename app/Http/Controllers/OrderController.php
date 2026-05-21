<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Auth;
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
            ->with('products', 'products.mainImage:id,product_id,image_path')
            ->get();

        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user_id = Auth::id();
        $validated = $request->validate([
            'total_price' => 'required|numeric',
            'note' => 'nullable|string',
            'products' => 'required|array|min:1',
            'product.*.id' => 'required|integer|exists;products,id',
            'product.*.quantity' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $user_id,
                'total_price' => $validated['total_price'],
                'note' => $validated['note'],
                'status' => 'pending'
            ]);
            $pivotData = [];
            foreach ($validated['products'] as $item) {

                $product = Product::find($item['quantity']);
                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'message' => "{$product->name} mahsulot yetarli emas!"
                    ], 400);
                }

                $pivotData[$item['id']] =[
                    'quantity' => $item['quantity'],
                    'price' => $product->price
                ];

                $product->decrement('stock', $item['quantity']);
            }

            $order->products()->attach($pivotData);

            DB::commit();

            $order->load('products');

            return response()->json([
                'message' => 'Buyurtma muvaffaqiyatli qabul qilindi',
                'data' => $order
            ],201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => 'Xatolik yuz berdi',
                'data' => $e->getMessage()
            ],500);
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
