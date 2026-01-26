<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCoursesController extends Controller
{
    private function mustBeSuperAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'SuperAdmin') {
            abort(403, 'Forbidden');
        }
    }

    // GET /api/admin/courses
    public function index()
    {
        $this->mustBeSuperAdmin();

        $courses = Course::query()
            ->with(['instructor:id,fullName,email'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $courses]);
    }

    // PUT /api/admin/courses/{course}
    public function update(Request $request, Course $course)
    {
        $this->mustBeSuperAdmin();

        $validated = $request->validate([
            'isPublished' => ['required', 'boolean'],
        ]);

        $course->update([
            'isPublished' => (bool)$validated['isPublished'],
        ]);

        return response()->json(['data' => $course]);
    }
}
