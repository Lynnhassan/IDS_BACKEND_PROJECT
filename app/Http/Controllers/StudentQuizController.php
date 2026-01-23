<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Course;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentQuizController extends Controller
{
    /**
     * Display all available quizzes for a course
     */
    public function getCourseQuizzes($courseId)
    {
        $course = Course::findOrFail($courseId);

        $quizzes = Quiz::where('courseId', $courseId)
            ->withCount('questions')
            ->get()
            ->map(function ($quiz) {
                $attemptCount = QuizAttempt::where('quizId', $quiz->id)
                    ->where('userId', Auth::id())
                    ->count();

                $bestScore = QuizAttempt::where('quizId', $quiz->id)
                    ->where('userId', Auth::id())
                    ->max('score');

                $hasPassed = $bestScore >= $quiz->passingScore;

                $quiz->user_attempts = $attemptCount;
                $quiz->attempts_left = max(0, $quiz->maxAttempts - $attemptCount);
                $quiz->best_score = $bestScore ?? 0;
                $quiz->has_passed = $hasPassed;
                $quiz->can_attempt = $attemptCount < $quiz->maxAttempts && (!$bestScore || $bestScore < 90);

                return $quiz;
            });

        return response()->json([
            'success' => true,
            'course' => $course,
            'quizzes' => $quizzes
        ]);
    }

    /**
     * Get quiz details before taking (preview)
     */
    public function getQuizPreview($quizId)
    {
        $quiz = Quiz::with(['course'])
            ->withCount('questions')
            ->findOrFail($quizId);

        $attempts = QuizAttempt::where('quizId', $quizId)
            ->where('userId', Auth::id())
            ->orderBy('attemptDate', 'desc')
            ->get();

        $attemptCount = $attempts->count();
        $bestScore = $attempts->max('score') ?? 0;
        $hasPassed = $bestScore >= $quiz->passingScore;
        $canAttempt = $attemptCount < $quiz->maxAttempts && $bestScore < 90;

        return response()->json([
            'success' => true,
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'course' => $quiz->course,
                'passingScore' => $quiz->passingScore,
                'timeLimit' => $quiz->timeLimit,
                'maxAttempts' => $quiz->maxAttempts,
                'totalQuestions' => $quiz->questions_count,
            ],
            'student_progress' => [
                'attempts_taken' => $attemptCount,
                'attempts_left' => max(0, $quiz->maxAttempts - $attemptCount),
                'best_score' => $bestScore,
                'has_passed' => $hasPassed,
                'can_attempt' => $canAttempt
            ],
            'attempt_history' => $attempts->map(function ($attempt) use ($quiz) {
                return [
                    'id' => $attempt->id,
                    'score' => $attempt->score,
                    'attempt_date' => $attempt->attemptDate,
                    'passed' => $attempt->score >= $quiz->passingScore
                ];
            })
        ]);
    }

    /**
     * Start/Take quiz - Get questions and answers
     */
    public function startQuiz($quizId)
    {
        $quiz = Quiz::with(['questions.answers', 'course'])
            ->findOrFail($quizId);

        $attemptCount = QuizAttempt::where('quizId', $quizId)
            ->where('userId', Auth::id())
            ->count();

        $bestScore = QuizAttempt::where('quizId', $quizId)
            ->where('userId', Auth::id())
            ->max('score');

        if ($bestScore >= 90) {
            return response()->json([
                'success' => false,
                'message' => 'You have already achieved an excellent score of ' . round($bestScore, 2) . '%. Retakes are not available for scores 90% or above.',
                'best_score' => $bestScore
            ], 403);
        }

        if ($attemptCount >= $quiz->maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum number of attempts for this quiz.',
                'attempts_used' => $attemptCount,
                'max_attempts' => $quiz->maxAttempts
            ], 403);
        }

        $questions = $quiz->questions->map(function ($question) {
            return [
                'id' => $question->id,
                'questionText' => $question->questionText,
                'questionType' => $question->questionType,
                'answers' => $question->answers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'answerText' => $answer->answerText
                    ];
                })->shuffle()
            ];
        });

        return response()->json([
            'success' => true,
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'course' => $quiz->course->name ?? 'N/A',
                'passingScore' => $quiz->passingScore,
                'timeLimit' => $quiz->timeLimit,
                'totalQuestions' => $questions->count()
            ],
            'questions' => $questions,
            'attempt_info' => [
                'attempt_number' => $attemptCount + 1,
                'attempts_left_after' => $quiz->maxAttempts - $attemptCount - 1
            ]
        ]);
    }

    /**
     * Submit quiz and calculate score
     */
    public function submitQuiz(Request $request, $quizId)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required'
        ]);

        $quiz = Quiz::with(['questions.answers', 'course'])->findOrFail($quizId);

        $attemptCount = QuizAttempt::where('quizId', $quizId)
            ->where('userId', Auth::id())
            ->count();

        if ($attemptCount >= $quiz->maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum attempts exceeded'
            ], 403);
        }

        $studentAnswers = $request->input('answers', []);
        $correctAnswers = 0;
        $totalQuestions = $quiz->questions->count();
        $questionResults = [];

        // Calculate score
        foreach ($quiz->questions as $question) {
            $userAnswer = $studentAnswers[$question->id] ?? null;
            $isCorrect = false;

            $answerOptions = $question->answers->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'answer_text' => $answer->answerText,
                    'is_correct' => $answer->isCorrect
                ];
            });

            if ($question->questionType === 'single') {
                $correctAnswer = $question->answers->where('isCorrect', true)->first();

                if ($correctAnswer && $userAnswer == $correctAnswer->id) {
                    $correctAnswers++;
                    $isCorrect = true;
                }

                $questionResults[] = [
                    'question_id' => $question->id,
                    'question_text' => $question->questionText,
                    'question_type' => $question->questionType,
                    'is_correct' => $isCorrect,
                    'user_answer' => $userAnswer,
                    'correct_answer' => $correctAnswer->id ?? null,
                    'answers' => $answerOptions
                ];

            } else {
                $correctAnswerIds = $question->answers->where('isCorrect', true)->pluck('id')->toArray();
                $userAnswerArray = is_array($userAnswer) ? $userAnswer : [];

                sort($correctAnswerIds);
                sort($userAnswerArray);

                if ($correctAnswerIds == $userAnswerArray) {
                    $correctAnswers++;
                    $isCorrect = true;
                }

                $questionResults[] = [
                    'question_id' => $question->id,
                    'question_text' => $question->questionText,
                    'question_type' => $question->questionType,
                    'is_correct' => $isCorrect,
                    'user_answer' => $userAnswerArray,
                    'correct_answer' => $correctAnswerIds,
                    'answers' => $answerOptions
                ];
            }
        }

        // Calculate percentage score
        $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
        $passed = $score >= $quiz->passingScore;

        // Save quiz attempt
        $attempt = QuizAttempt::create([
            'quizId' => $quizId,
            'userId' => Auth::id(),
            'score' => $score,
            'attemptDate' => now()
        ]);

        $newAttemptCount = $attemptCount + 1;
        $attemptsLeft = $quiz->maxAttempts - $newAttemptCount;

        // ✅ FIXED: Single, clean certificate generation block
        $certificateGenerated = false;
        $certificateId = null;

        if ($score >= 90) {
            try {
                $course = $quiz->course;

                // Use firstOrCreate to avoid duplicates
                $certificate = Certificate::firstOrCreate(
                    [
                        'userId' => Auth::id(),
                        'courseId' => $course->id,
                    ]
                );

                // Check if it was just created (not previously existing)
                $certificateGenerated = $certificate->wasRecentlyCreated;
                $certificateId = $certificate->id;

                if ($certificateGenerated) {
                    \Log::info('Certificate auto-generated', [
                        'user_id' => Auth::id(),
                        'course_id' => $course->id,
                        'certificate_id' => $certificate->id,
                        'quiz_score' => $score
                    ]);
                }

            } catch (\Exception $e) {
                \Log::error('Failed to auto-generate certificate: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'result' => [
                'attempt_id' => $attempt->id,
                'score' => round($score, 2),
                'passed' => $passed,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'passing_score' => $quiz->passingScore,
                'certificate_generated' => $certificateGenerated,
                'certificate_id' => $certificateId
            ],
            'quiz_info' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'course_id' => $quiz->courseId
            ],
            'attempts_info' => [
                'attempts_used' => $newAttemptCount,
                'attempts_left' => max(0, $attemptsLeft),
                'max_attempts' => $quiz->maxAttempts,
                'can_retake' => $attemptsLeft > 0 && $score < 90
            ],
            'question_results' => $questionResults
        ]);
    }

    /**
     * Get specific attempt details
     */
    public function getAttemptDetails($attemptId)
    {
        $attempt = QuizAttempt::with(['quiz', 'user'])
            ->where('userId', Auth::id())
            ->findOrFail($attemptId);

        return response()->json([
            'success' => true,
            'attempt' => [
                'id' => $attempt->id,
                'quiz' => [
                    'id' => $attempt->quiz->id,
                    'title' => $attempt->quiz->title
                ],
                'score' => $attempt->score,
                'passed' => $attempt->score >= $attempt->quiz->passingScore,
                'attempt_date' => $attempt->attemptDate->format('Y-m-d H:i:s'),
                'formatted_date' => $attempt->attemptDate->diffForHumans()
            ]
        ]);
    }

    /**
     * Get all student's quiz attempts across all courses
     */
    public function getAllAttempts()
    {
        $attempts = QuizAttempt::with(['quiz.course'])
            ->where('userId', Auth::id())
            ->orderBy('attemptDate', 'desc')
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'quiz_title' => $attempt->quiz->title,
                    'course_name' => $attempt->quiz->course->name ?? 'N/A',
                    'score' => $attempt->score,
                    'passed' => $attempt->score >= $attempt->quiz->passingScore,
                    'attempt_date' => $attempt->attemptDate->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'success' => true,
            'total_attempts' => $attempts->count(),
            'attempts' => $attempts
        ]);
    }

    /**
     * Get student's dashboard statistics
     */
    public function getDashboard()
    {
        $userId = Auth::id();

        $totalQuizzesTaken = QuizAttempt::where('userId', $userId)
            ->distinct('quizId')
            ->count('quizId');

        $totalAttempts = QuizAttempt::where('userId', $userId)->count();
        $averageScore = QuizAttempt::where('userId', $userId)->avg('score');

        $passedQuizzes = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quizId', '=', 'quizzes.id')
            ->where('quiz_attempts.userId', $userId)
            ->whereRaw('quiz_attempts.score >= quizzes.passingScore')
            ->distinct()
            ->count('quiz_attempts.quizId');

        $recentAttempts = QuizAttempt::with(['quiz.course'])
            ->where('userId', $userId)
            ->orderBy('attemptDate', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($attempt) {
                return [
                    'quiz_title' => $attempt->quiz->title,
                    'course_name' => $attempt->quiz->course->name ?? 'N/A',
                    'score' => $attempt->score,
                    'passed' => $attempt->score >= $attempt->quiz->passingScore,
                    'date' => $attempt->attemptDate->diffForHumans()
                ];
            });

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_quizzes_taken' => $totalQuizzesTaken,
                'total_attempts' => $totalAttempts,
                'average_score' => round($averageScore ?? 0, 2),
                'quizzes_passed' => $passedQuizzes,
                'pass_rate' => $totalQuizzesTaken > 0
                    ? round(($passedQuizzes / $totalQuizzesTaken) * 100, 2)
                    : 0
            ],
            'recent_attempts' => $recentAttempts
        ]);
    }

    /**
     * Get attempt history for a specific quiz
     */
    public function getAttemptHistory($quizId)
    {
        try {
            $quiz = Quiz::findOrFail($quizId);

            $attempts = QuizAttempt::where('quizId', $quizId)
                ->where('userId', Auth::id())
                ->orderBy('attemptDate', 'desc')
                ->get()
                ->map(function ($attempt) use ($quiz) {
                    $attemptDate = $attempt->attemptDate;

                    if (is_string($attemptDate)) {
                        $attemptDate = Carbon::parse($attemptDate);
                    }

                    return [
                        'id' => $attempt->id,
                        'score' => $attempt->score,
                        'passed' => $attempt->score >= $quiz->passingScore,
                        'attempt_date' => $attemptDate->format('Y-m-d H:i:s'),
                        'formatted_date' => $attemptDate->diffForHumans()
                    ];
                });

            $bestScore = $attempts->max('score') ?? 0;
            $averageScore = $attempts->avg('score') ?? 0;

            return response()->json([
                'success' => true,
                'quiz' => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'passing_score' => $quiz->passingScore,
                    'courseId' => $quiz->courseId
                ],
                'statistics' => [
                    'total_attempts' => $attempts->count(),
                    'best_score' => round($bestScore, 2),
                    'average_score' => round($averageScore, 2),
                    'has_passed' => $bestScore >= $quiz->passingScore
                ],
                'attempts' => $attempts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getAttemptHistory: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quiz history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
