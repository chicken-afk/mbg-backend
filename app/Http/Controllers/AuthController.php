<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Credentials for authentication
        $credentials = $request->only('email', 'password');

        try {
            // Attempt to generate a token
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
        //Update user last login
        User::where("id", auth()->user()->id)->update([
            "last_login_at" => now()
        ]);
        $user = User::where("email", $request->email)->first();
        $output = [
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'last_login_at' => $user->last_login_at,
                ],
            ],
        ];
        // Return the token and user information
        return response()->json($output, 200);
    }
}
