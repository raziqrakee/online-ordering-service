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
        } else {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->storeAs(
                    'public/storage/images', // Save in the desired directory
                    $request->file('image')->getClientOriginalName() // Use the original file name
                );
                $imagePath = str_replace('public/', '', $imagePath); // Remove 'public/' from path
            }

            $products = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'category' => $request->category,
                'image' => $imagePath,
            ]);

            if ($products) {
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
        } else {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->storeAs(
                    'public/storage/images', // Save in the desired directory
                    $request->file('image')->getClientOriginalName() // Use the original file name
                );
                $imagePath = str_replace('public/', '', $imagePath); // Remove 'public/' from path
            }
            $products = Product::find($id);

            if ($products) {

                $products->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'price' => $request->price,
                    'quantity' => $request->quantity,
                    'category' => $request->category,
                    'image' => $imagePath,
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
}
