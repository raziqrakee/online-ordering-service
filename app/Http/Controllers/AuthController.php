<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'contact_number' => 'required|string|regex:/^[0-9]{10,11}$/',
            'role' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        $token = $user->createToken('AuthToken')->plainTextToken;

        return response()->json(['token' => $token, 'role' => $request->role], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('AuthToken')->plainTextToken;

        return response()->json(['token' => $token, 'role' => $user->role, 'id' => $user->id], 200);
    }

    public function logout(Request $request)
    {
        // Ensure that the authenticated user's tokens are deleted
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully'], 200);
        }

        return response()->json(['message' => 'Unable to logout'], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $tempPassword = Str::random(10);
        $user->password = Hash::make($tempPassword);
        $user->save();

        $this->sendTempPasswordByEmail($user->email, $tempPassword);

        return response()->json(['message' => 'Temporary password sent to your email'], 200);
    }

    private function sendTempPasswordByEmail($email, $tempPassword)
    {
        Mail::raw("Your temporary password is: $tempPassword", function ($message) use ($email) {
            $message->from('your@example.com', 'Admin Danish Ice Cream Cafe');
            $message->to($email)->subject('Temporary Password');
        });
    }
}
