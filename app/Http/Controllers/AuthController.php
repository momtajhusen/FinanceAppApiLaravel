<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create and return a new access token
        $token = $user->createToken('authToken')->accessToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    // Login existing user
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        // Create and return a new access token
        $token = $user->createToken('authToken')->accessToken;

        return response()->json(['token' => $token, 'user' => $user], 200);
    }

    // Logout user (invalidate the token)
    public function logout(Request $request)
    {
        // Revoke all tokens for the authenticated user
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }

    // Verify token
    public function verifyToken(Request $request)
    {
        // Check if the user is authenticated
        $user = Auth::guard('api')->user();
        
        if ($user) {
            // Return success response with user information
            return response()->json(['message' => 'Token is valid', 'user' => $user], 200);
        } else {
            // Return error response if token is invalid
            return response()->json(['message' => 'Invalid token'], 401);
        }
    }

}
