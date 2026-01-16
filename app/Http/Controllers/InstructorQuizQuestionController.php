<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorQuizQuestionController extends Controller
{
    // GET: /instructor/courses/{courseId}/quizzes/{quizId}/questions
    public function index($courseId, $quizId)
    {
        $user = Auth::user();

        $course = Course::where('id', $courseId)
            ->where('instructorId', $user->id)
            ->firstOrFail();

        $quiz = Quiz::where('id', $quizId)
            ->where('courseId', $course->id)
            ->firstOrFail();

        $questions = Question::where('quizId', $quiz->id)
            ->with('answers')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'quiz' => $quiz,
            'questions' => $questions
        ]);
    }

    // POST: /instructor/courses/{courseId}/quizzes/{quizId}/questions
    public function store(Request $request, $courseId, $quizId)
    {
        $user = Auth::user();

        $course = Course::where('id', $courseId)
            ->where('instructorId', $user->id)
            ->firstOrFail();

        $quiz = Quiz::where('id', $quizId)
            ->where('courseId', $course->id)
            ->firstOrFail();

        $validated = $request->validate([
            'questionText' => 'required|string|max:255',
            'questionType' => 'required|in:single,multiple',
            'answers' => 'required|array|min:2|max:10',
            'answers.*.answerText' => 'required|string|max:255',
            'answers.*.isCorrect' => 'required|boolean',
        ]);

        // ✅ Validate correct answers count
        $correctCount = collect($validated['answers'])->where('isCorrect', true)->count();
        if ($validated['questionType'] === 'single' && $correctCount !== 1) {
            return response()->json(['error' => 'Single choice must have exactly 1 correct answer'], 422);
        }
        if ($validated['questionType'] === 'multiple' && $correctCount < 1) {
            return response()->json(['error' => 'Multiple choice must have at least 1 correct answer'], 422);
        }

        // Create question
        $question = Question::create([
            'quizId' => $quiz->id,
            'questionText' => $validated['questionText'],
            'questionType' => $validated['questionType'],
        ]);

        // Create answers
        foreach ($validated['answers'] as $ans) {
            Answer::create([
                'questionId' => $question->id,
                'answerText' => $ans['answerText'],
                'isCorrect' => (bool)$ans['isCorrect'],
            ]);
        }

        // return created question with answers
        return response()->json(
            Question::where('id', $question->id)->with('answers')->first(),
            201
        );
    }
}
