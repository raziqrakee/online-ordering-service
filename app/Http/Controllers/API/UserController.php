<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'contact_number' => 'required|string|regex:/^[0-9]{10,11}$/',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->contact_number = $request->contact_number;
        $user->password = bcrypt($request->password);
        $user->role = $request->role;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $user->image = 'images/' . $imageName;
        }

        $user->save();

        return response()->json(['message' => 'User created successfully'], 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $user->image_url = $user->image ? asset($user->image) : null;
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'contact_number' => 'required|string|regex:/^[0-9]{10,11}$/',
            'password' => 'nullable|string|min:6',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'data' => $request->all()], 400);
        }

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->contact_number = $request->contact_number;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/images'), $imageName);
            $user->image = 'storage/images/' . $imageName;
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function removeImage($id)
    {
        $user = User::findOrFail($id);

        if ($user->image) {
            $user->image = null;
            $user->save();

            return response()->json(['message' => 'User image removed successfully'], 200);
        }

        return response()->json(['message' => 'User has no image to remove'], 404);
    }
}
