<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function p_index()
    {
        $products = Product::all();
        if ($products->count() > 0) {
            return response()->json([
                'status' => 200,
                'products' => $products
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Records Found'
            ], 404);
        }
    }

    public function p_store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/product_images');
            $imagePath = str_replace('public/', '', $imagePath);
        }
    
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'category' => $request->category,
            'image' => $imagePath,
        ]);
    
        if ($product) {
            return response()->json([
                'status' => 200,
                'message' => 'Product Added Successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something Went Wrong!'
            ], 500);
        }
    }
    

    public function p_show($id)
    {
        $products = Product::find($id);
        if ($products){
            return response()->json([
                'status' => 200,
                'product' => $products
            ], 200);
        }else{
                return response()->json([
                    'status' => 404,
                    'message' => 'No Such Product Found!'
                ], 404);
        }
    }

    public function p_edit($id)
    {
        $products = Product::find($id);
        if ($products){
            return response()->json([
                'status' => 200,
                'product' => $products
            ], 200);
        }else{
                return response()->json([
                    'status' => 404,
                    'message' => 'No Such Product Found!'
                ], 404);
        }
    }

    public function p_update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/product_images');
            $imagePath = str_replace('public/', '', $imagePath);
        }
    
        $product = Product::find($id);
    
        if ($product) {
            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'category' => $request->category,
                'image' => $imagePath ?: $product->image, // Keep the existing image if no new image is uploaded
            ]);
    
            return response()->json([
                'status' => 200,
                'message' => 'Product Updated Successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Such Product Found!'
            ], 404);
        }
    }
    
    public function p_destroy($id)
    {
        $products = Product::find($id);
        if ($products){
            $products->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Product Deleted Successfully'
            ], 200);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'No Such Product Found!'
            ], 404);
        }

    }
    public function p_purchase(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($id);

        if ($product) {
            if ($product->quantity >= $request->quantity) {
                $product->quantity -= $request->quantity;
                $product->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Purchase successful, quantity deducted',
                    'product' => $product
                ], 200);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Insufficient product quantity'
                ], 400);
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found'
            ], 404);
        }
    }
}
