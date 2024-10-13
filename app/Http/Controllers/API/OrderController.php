<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required',
            'order_type' => 'required',
            'total_amount' => 'required|numeric',
            'special_instructions' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();

        $order = new Order();
        $order->user_id = $user->id;
        $order->total_amount = $request->total_amount;
        $order->order_status = 'pending';
        $order->customer_order_status = 'received';
        $order->payment_method = $request->payment_method;
        $order->order_type = $request->order_type;
        $order->special_instructions = $request->special_instructions;

        if ($request->hasFile('receipt')) {
            $receipt = $request->file('receipt');
            $receiptName = time() . '.' . $receipt->getClientOriginalExtension();
            $receipt->move(public_path('receipts'), $receiptName);
            $order->receipt_path = 'receipts/' . $receiptName;
        }

        $order->save();

        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);

            // Update the sold quantity for the product
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->increment('sold', $item['quantity']);
                $product->decrement('quantity', $item['quantity']);
            }
        }

        return response()->json(['id' => $order->id, 'message' => 'Order placed successfully'], 201);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'order_status' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $order = Order::findOrFail($id);

        $statusMap = [
            'pending' => 'received',
            'in-process' => 'preparing',
            'completed' => 'ready',
            'cancelled' => 'Please Order Again'
        ];

        $order->order_status = $request->order_status;
        $order->customer_order_status = $statusMap[$request->order_status] ?? $order->customer_order_status;
        $order->save();

        return response()->json(['order_status' => $order->order_status, 'customer_order_status' => $order->customer_order_status, 'message' => 'Order status updated successfully'], 200);
    }

    public function viewOrder($id)
    {
        $order = Order::with('items.product', 'user')->findOrFail($id);
        return response()->json($order);
    }

    public function listOrders()
    {
        $orders = Order::with('user', 'items.product')->get();
        return response()->json($orders);
    }

    public function viewOrderDetails($id)
    {
        $order = Order::with('user', 'items.product')->findOrFail($id);
        return response()->json($order);
    }

    public function getLatestOrderId()
    {
        $latestOrder = Order::orderBy('id', 'desc')->first();
        $latestOrderId = $latestOrder ? $latestOrder->id : 0;
        return response()->json(['latestOrderId' => $latestOrderId], 200);
    }

    public function getUserLatestOrder()
    {
        $user = Auth::user();
        $latestOrder = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();

        if ($latestOrder) {
            return response()->json(['order_id' => $latestOrder->id], 200);
        } else {
            return response()->json(['message' => 'No orders found'], 404);
        }
    }
}
