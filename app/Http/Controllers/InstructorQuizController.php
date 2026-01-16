<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorQuizController extends Controller
{
    // GET /instructor/courses/{courseId}/quizzes
    public function index($courseId)
    {
        $user = Auth::user();

        $course = Course::where('id', $courseId)
            ->where('instructorId', $user->id)
            ->firstOrFail();

        $quizzes = Quiz::where('courseId', $course->id)->orderByDesc('id')->get();

        return response()->json($quizzes);
    }

    // POST /instructor/courses/{courseId}/quizzes
    public function store(Request $request, $courseId)
    {
        $user = Auth::user();

        $course = Course::where('id', $courseId)
            ->where('instructorId', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'passingScore' => 'required|numeric|min:0|max:100',
            'timeLimit' => 'required|integer|min:1',
            'maxAttempts' => 'required|integer|min:1',
        ]);

        $quiz = Quiz::create([
            'courseId' => $course->id,
            'title' => $validated['title'],
            'passingScore' => $validated['passingScore'],
            'timeLimit' => $validated['timeLimit'],
            'maxAttempts' => $validated['maxAttempts'],
        ]);

        return response()->json($quiz, 201);
    }
}
