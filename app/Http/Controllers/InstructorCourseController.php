<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // ✅ IMPORTANT (you were missing this)
use Illuminate\Validation\Rule;

class InstructorCourseController extends Controller
{
    // PUT/POST /api/instructor/courses/{course}
    public function update(Request $request, Course $course)
    {
        $user = Auth::user();

        if ($user->role !== 'Instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($course->instructorId !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // ✅ Fix boolean issue: accept 0/1 instead of boolean strings from FormData
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'shortDescription' => 'required|string|max:150',
            'longDescription' => 'required|string',
            'category' => 'required|string|max:100',
            'difficulty' => ['required', Rule::in(['Easy', 'Medium', 'Hard'])],
            'thumbnail' => 'nullable|string|max:255',

            // ✅ IMPORTANT: use in:0,1 not boolean (FormData sends strings)
            'isPublished' => 'nullable|in:0,1',

            'pdf' => 'nullable|file|mimes:pdf|max:10240',
            'remove_pdf' => 'nullable|in:0,1',
        ]);

        // ✅ remove old pdf if requested
        if ($request->input('remove_pdf') == '1') {
            if ($course->pdf) {
                Storage::disk('public')->delete($course->pdf);
            }
            $validated['pdf'] = null;
        }

        // ✅ upload new pdf (replaces old)
        if ($request->hasFile('pdf')) {
            if ($course->pdf) {
                Storage::disk('public')->delete($course->pdf);
            }
            $validated['pdf'] = $request->file('pdf')->store('course_pdfs', 'public');
        }

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
            'longDescription' => ['required', 'string'],
            'category' => ['required', 'string'],
            'difficulty' => ['required', Rule::in(['Easy', 'Medium', 'Hard'])],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $pdfPath = null;

        if ($request->hasFile('pdf')) {
            $pdfPath = $request->file('pdf')->store('course_pdfs', 'public');
        }

        $course = Course::create([
            'title' => $validated['title'],
            'shortDescription' => $validated['shortDescription'],
            'longDescription' => $validated['longDescription'],
            'category' => $validated['category'],
            'difficulty' => $validated['difficulty'],
            'thumbnail' => $validated['thumbnail'] ?? null,
            'pdf' => $pdfPath,
            'instructorId' => $user->id,
            'isPublished' => 0,
        ]);

        return response()->json([
            'data' => $course
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

        $course->load(['lessons' => function ($q) {
            $q->orderBy('order');
        }]);

        return response()->json($course);
    }
}
