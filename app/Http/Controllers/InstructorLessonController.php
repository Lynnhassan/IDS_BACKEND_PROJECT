<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InstructorLessonController extends Controller
{
    // GET /api/instructor/courses/{course}/lessons
    public function index(Course $course)
    {
        $user = Auth::user();

        if ($user->role !== 'Instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($course->instructorId !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $lessons = Lesson::where('courseId', $course->id)
            ->orderBy('order')
            ->get();

        return response()->json($lessons);
    }

    // POST /api/instructor/courses/{course}/lessons
    public function store(Request $request, Course $course)
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
            'contentType' => ['required', Rule::in(['Video', 'Article', 'Quiz'])],
            'videoUrl' => 'nullable|string|max:255',
            'estimatedDuration' => 'required|numeric|min:0',
            'order' => 'required|integer|min:1',
        ]);

        $lesson = Lesson::create([
            ...$validated,
            'courseId' => $course->id,
        ]);

        return response()->json([
            'message' => 'Lesson created',
            'lesson' => $lesson
        ], 201);
    }
}
