<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => auth('api')->user(),
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // auto-hashed by User model
            'role'     => $data['role'] ?? 'user',
        ]);

        $token = JWTAuth::fromUser($user);
        return $this->respondWithToken($token);
    }

    public function login(LoginRequest $request)
    {
        $creds = $request->validated();

        if (! $token = auth('api')->attempt($creds)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'logged_out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
}
