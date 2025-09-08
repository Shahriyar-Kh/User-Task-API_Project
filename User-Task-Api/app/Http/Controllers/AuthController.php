<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected function respondWithToken($token)
    {
        $user = auth('api')->user();

        // Return minimal user payload (avoid exposing sensitive fields)
        $userPayload = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ];

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => $userPayload,
        ]);
    }

  public function register(RegisterRequest $request)
{
    $data = $request->validated();

    if (User::where('email', $data['email'])->exists()) {
        return response()->json(['message' => 'User already registered'], 422);
    }

    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => $data['password'], // hashed in User model
        'role'     => $data['role'] ?? 'user',
    ]);

    // ✅ Create JWT token for the new user
    $token = JWTAuth::fromUser($user);

    return response()->json([
        'access_token' => $token,
        'token_type'   => 'bearer',
        'expires_in'   => auth('api')->factory()->getTTL() * 60,
        'user'         => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ],
    ], 201);
}

public function login(Request $request)
{
    try {
        // Step 1: Basic validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->email;
        $password = $request->password;
        
        // Step 2: Check if email exists in database
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Email is not exist'
            ], 404);
        }

        // Step 3: Email exists, now check if password matches
        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Password is wrong'
            ], 401);
        }

        // Step 4: Both email and password are correct - Generate JWT token
        $token = auth('api')->login($user);
        if (!$token) {
            return response()->json(['message' => 'Login failed'], 401);
        }

        // Step 5: Return success with user data for role-based redirect
        return response()->json([
            'access_token' => $token,  // ✅ Frontend expects this field name
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ], 200);

    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        \Illuminate\Support\Facades\Log::error('JWT Error: ' . $e->getMessage());
        return response()->json(['message' => 'Could not create token'], 500);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Login error: ' . $e->getMessage());
        return response()->json(['message' => 'Login failed: ' . $e->getMessage()], 500);
    }
}

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
}