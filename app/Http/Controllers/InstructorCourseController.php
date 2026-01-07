<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InstructorCourseController extends Controller
{



    public function update(Request $request, Course $course)
    {
        $user = Auth::user();

        if ($user->role !== 'Instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($course->instructorId !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'shortDescription' => 'required|string|max:150',
            'longDescription' => 'required|string',
            'category' => 'required|string|max:100',
            'difficulty' => ['required', Rule::in(['Easy', 'Medium', 'Hard'])],
            'thumbnail' => 'nullable|string|max:255',
            'isPublished' => 'nullable|boolean',
        ]);

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course
        ]);
    }

    // GET /api/instructor/courses
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'Instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $courses = Course::where('instructorId', $user->id)
            ->orderByDesc('id')
            ->get();

        return response()->json($courses);
    }

    // POST /api/instructor/courses
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'Instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'shortDescription' => ['required', 'string', 'max:150'],
            'longDescription' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string'],
            'difficulty' => ['required', Rule::in(['Easy', 'Medium', 'Hard'])],
            'thumbnail' => ['nullable', 'string'],
        ]);

        $course = Course::create([
            'title' => $validated['title'],
            'shortDescription' => $validated['shortDescription'],
            'longDescription' => $validated['longDescription'],
            'category' => $validated['category'],
            'difficulty' => $validated['difficulty'],
            'thumbnail' => $validated['thumbnail'] ?? null,
            'instructorId' => $user->id,
            'isPublished' => false,
        ]);

        return response()->json([
            'id' => $course->id,
            'course' => $course
        ], 201);
    }

    // GET /api/instructor/courses/{course}
    public function show(Course $course)
    {
        $user = Auth::user();

        if ($user->role !== 'Instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($course->instructorId !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // ✅ include lessons ordered
        $course->load(['lessons' => function ($q) {
            $q->orderBy('order');
        }]);

        return response()->json($course);
    }

}
