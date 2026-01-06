<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'fullName' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:3|confirmed',
            'role' => 'required|in:Student,Instructor,SuperAdmin',
        ]);

        $user = User::create([
            'fullName' => $validated['fullName'],
            'email' => $validated['email'],
            'password' => $validated['password'], // auto-hashed
            'role' => $validated['role'],
            'isActive' => true,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Incorrect password'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user
        ]);
    }
}
