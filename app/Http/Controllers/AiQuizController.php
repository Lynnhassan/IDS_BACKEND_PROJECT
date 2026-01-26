<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use App\Services\QuizGenerator;

class AiQuizController extends Controller
{
    // 1. Send suggestions to React

    public function suggest($courseId, QuizGenerator $generator)
    {
        $course = Course::findOrFail($courseId);

        if (!$course->pdf) {
            return response()->json(['error' => 'No PDF file found for this course.'], 404);
        }

        try {
            $data = $generator->generateFromPdf($course->pdf);

            if (!isset($data['questions']) || empty($data['questions'])) {
                return response()->json([
                    'error' => 'AI returned no questions. Check the PDF content.',
                    'debug' => $data // return raw generator output for debugging
                ], 200);
            }

            return response()->json([
                'course' => $course,
                'suggestedQuestions' => $data['questions']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception during quiz generation: ' . $e->getMessage()
            ], 500);
        }
    }


    public function bulkSaveQuestions(Request $request, Course $course, Quiz $quiz) {
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.questionText' => 'required|string',
            'questions.*.questionType' => 'required|string',
            'questions.*.answers' => 'required|array|min:1',
            'questions.*.answers.*.answerText' => 'required|string',
            'questions.*.answers.*.isCorrect' => 'required|boolean',
        ]);

        foreach ($validated['questions'] as $q) {
            $question = $quiz->questions()->create([
                'questionText' => $q['questionText'],
                'questionType' => $q['questionType'],
            ]);

            foreach ($q['answers'] as $a) {
                $question->answers()->create($a);
            }
        }

        return response()->json([
            'message' => 'Questions saved successfully',
        ], 201);
    }

//    public function suggest($courseId, QuizGenerator $generator)
//    {
//        $course = Course::findOrFail($courseId);
//
//        if (!$course->pdf) {
//            return response()->json(['error' => 'No PDF file found for this course.'], 404);
//        }
//
//        try {
//            $data = $generator->generateFromPdf($course->pdf);
//
//            // Return JSON for React to map through
//            return response()->json([
//                'course' => $course,
//                'suggestedQuestions' => $data['questions']
//            ]);
//        } catch (\Exception $e) {
//            return response()->json(['error' => $e->getMessage()], 500);
//        }
//    }

//    // 2. Receive accepted questions from React
//    public function store(Request $request, $courseId)
//    {
//        // Validating the incoming JSON from React
//        $validated = $request->validate([
//            'quiz_title' => 'required|string',
//            'questions' => 'required|array',
//            'questions.*.questionText' => 'required|string',
//            'questions.*.answers' => 'required|array',
//        ]);
//
//        // Create the Quiz record
//        $quiz = Quiz::create([
//            'courseId' => $courseId,
//            'title' => $validated['quiz_title'],
//            'passingScore' => 70,
//            'timeLimit' => 30,
//            'maxAttempts' => 3
//        ]);
//
//        foreach ($validated['questions'] as $qData) {
//            $question = Question::create([
//                'quizId' => $quiz->id,
//                'questionText' => $qData['questionText'],
//                'questionType' => $qData['questionType'] ?? 'single',
//            ]);
//
//            foreach ($qData['answers'] as $aData) {
//                Answer::create([
//                    'questionId' => $question->id,
//                    'answerText' => $aData['answerText'],
//                    'isCorrect' => (bool)$aData['isCorrect']
//                ]);
//            }
//        }
//
//        return response()->json(['message' => 'Quiz created and saved successfully!'], 201);

}
