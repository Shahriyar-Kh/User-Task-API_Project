<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // 🔹 Return logged-in user
    public function me(Request $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized. Please log in again.',
            ], 401);
        }

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    // 🔹 Update own profile
    public function update(Request $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'name'     => 'sometimes|string|max:100',
            'email'    => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|confirmed|min:8', // requires password_confirmation
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    // 🔹 List all users (admin only)
    public function index()
    {
        $authUser = auth('api')->user();

        if (!$authUser || $authUser->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $users = User::select('id', 'name', 'email', 'role')->get();

        return response()->json($users);
    }
}
