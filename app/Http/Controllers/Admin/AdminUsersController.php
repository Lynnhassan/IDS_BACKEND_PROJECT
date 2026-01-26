<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminUsersController extends Controller
{
    private function mustBeSuperAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'SuperAdmin') {
            abort(403, 'Forbidden');
        }
    }

    // GET /api/admin/users
    public function index()
    {
        $this->mustBeSuperAdmin();

        $users = User::query()
            ->orderByDesc('id')
            ->get(['id','fullName','email','role','isActive','created_at']);

        return response()->json(['data' => $users]);
    }

    // PUT /api/admin/users/{user}
    public function update(Request $request, User $user)
    {
        $this->mustBeSuperAdmin();

        $validated = $request->validate([
            'role' => ['nullable', Rule::in(['Student','Instructor','SuperAdmin'])],
            'isActive' => ['nullable', 'boolean'],
        ]);

        // If you don’t want SuperAdmin to be deactivated by mistake:
        // if ($user->role === 'SuperAdmin' && array_key_exists('isActive', $validated)) {
        //     return response()->json(['error' => 'Cannot deactivate SuperAdmin'], 422);
        // }

        $user->update($validated);

        return response()->json(['data' => $user]);
    }
}
