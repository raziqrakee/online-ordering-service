<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{
    public function p_index()
    {
        $products = Product::all();

        if ($products->count() > 0) {
            $productsWithImages = $products->map(function ($product) {
                if ($product->image && $product->image !== 'null') {
                    $imageUrl = asset('storage/images/' . basename($product->image));
                } else {
                    $imageUrl = "";
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'sold' => $product->sold,
                    'category' => $product->category,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                    'image_url' => $imageUrl
                ];
            });

            return response()->json([
                'status' => 200,
                'products' => $productsWithImages
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
            'description' => 'nullable|string',
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
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/images'), $imageName); // Adjust the path here
            $imagePath = 'storage/images/' . $imageName;
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'sold' => 0, // Initialize sold to 0
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

    // new api for insert product with image - 20240530
    public function p_insert(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
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
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/images'), $imageName);
            $imagePath = 'storage/images/' . $imageName;
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
    // new api for update product with image - 20240530
    public function product_update(Request $request, $id = null)
    {
        $data = $request->all();
        // Validate the request data
        $validator = Validator::make($data, [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/images'), $imageName);
            $imagePath = 'storage/images/' . $imageName;
        }

        try {
            if ($id) {
                // Edit operation
                $product = Product::findOrFail($id);
                $product->name = $request->name;
                $product->description = $request->description;
                $product->price = $request->price;
                $product->quantity = $request->quantity;
                $product->category = $request->category;
                $product->image = $request->image;
                if ($imagePath) {
                    $product->image = $imagePath;
                }
                $product->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Product Updated Successfully',
                    'data' => $product
                ], 200);
            } else {
                // Insert operation
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
                        'status' => 201,
                        'message' => 'Product Added Successfully',
                        'data' => $product
                    ], 201);
                } else {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Something Went Wrong!'
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in product_update: ' . $e->getMessage());

            // Return a generic error response
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while processing the request.'
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
            'description' => 'nullable|string',
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

        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found'
            ], 404);
        }

        if ($product->quantity < $request->quantity) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient product quantity'
            ], 400);
        }

        try {
            $product->decrement('quantity', $request->quantity);
            $product->increment('sold', $request->quantity);

            return response()->json([
                'status' => 200,
                'message' => 'Purchase successful, quantity deducted',
                'product' => $product
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in p_purchase: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while processing the purchase.'
            ], 500);
        }
    }
}
