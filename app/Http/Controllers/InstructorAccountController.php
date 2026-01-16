<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class InstructorAccountController extends Controller
{
    // ✅ View account info
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'Instructor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $user->id,
            'fullName' => $user->fullName,
            'email' => $user->email,
            'role' => $user->role,
            'isActive' => $user->isActive,
        ]);
    }

    // ✅ Change password
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'Instructor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // confirm old password
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        // update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }
}
