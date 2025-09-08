<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function me()
    {
        return response()->json([
            'user' => auth('api')->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth('api')->user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|confirmed|min:8', // needs password_confirmation
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }
}
