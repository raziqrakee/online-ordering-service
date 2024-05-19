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
            "contact_number" => 'required|string',
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

        return response()->json(['token' => $token, "role" => $user->role,  "id" => $user->id], 200);
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

         // Generate a random temporary password
         $tempPassword = Str::random(10);

         // Update user's password with the temporary password
         $user->password = Hash::make($tempPassword);
         $user->save();

         // Send the temporary password to the user's email
         $this->sendTempPasswordByEmail($user->email, $tempPassword);

         return response()->json(['message' => 'Temporary password sent to your email'], 200);
     }

     private function sendTempPasswordByEmail($email, $tempPassword)
     {
         // Send email logic here (use your preferred email sending method or service)
         // Example using Laravel Mail:
         Mail::raw("Your temporary password is: $tempPassword", function ($message) use ($email) {
            $message->from('your@example.com', 'Admin Danish Ice Cream Cafe');
            $message->to($email)->subject('Temporary Password');
        });

     }
}
