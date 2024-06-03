<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'product'])->get();
        return response()->json($orders, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $product = Product::find($request->product_id);

        if ($product->quantity < $request->quantity) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient product quantity'
            ], 400);
        }

        $totalPrice = $product->price * $request->quantity;

        $order = Order::create([
            'user_id' => $request->user_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        // Update product quantity and sold count
        $product->decrement('quantity', $request->quantity);
        $product->increment('sold', $request->quantity);

        return response()->json(['message' => 'Order placed successfully', 'order' => $order], 201);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'product'])->findOrFail($id);
        return response()->json($order, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}
