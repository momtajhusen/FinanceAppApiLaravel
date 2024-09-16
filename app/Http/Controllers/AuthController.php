<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Import Str class

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
            'api_token' => Str::random(60), // Set token here
        ]);

        return response()->json(['token' => $user->api_token, 'user' => $user], 201);
    }

    // Login existing user
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        // Generate and update the API token
        $user->api_token = Str::random(60);
        $user->save();

        return response()->json(['token' => $user->api_token, 'user' => $user], 200);
    }

    // Logout user (invalidate the token)
    public function logout(Request $request)
    {
        // Revoke all tokens for the authenticated user
        $request->user()->api_token = null;
        $request->user()->save();

        return response()->json(['message' => 'Successfully logged out']);
    }

    // Verify token
    public function verifyToken(Request $request)
    {
        $user = User::where('api_token', $request->bearerToken())->first();

        if ($user) {
            return response()->json(['message' => 'Token is valid', 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'Invalid token'], 401);
        }
    }
}
