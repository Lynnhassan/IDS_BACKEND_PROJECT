<?php

use App\Http\Controllers\Admin\AdminCoursesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstructorCourseController;
use App\Http\Controllers\InstructorLessonController;
use App\Http\Controllers\InstructorQuizQuestionController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorDashboardController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\InstructorQuizController;
use App\Http\Controllers\StudentQuizController;
use App\Http\Controllers\StudentCertificateController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\InstructorAccountController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUsersController;



// Test route
Route::get('/testt', function () {
    return response()->json(['message' => 'API works!']);
});

// Auth routes
Route::post('/signup', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', function () {
    return response()->json([
        'message' => 'Unauthenticated. Please use the API login endpoint.'
    ], 401);
})->name('login');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Instructor routes
Route::middleware('auth:sanctum')->prefix('instructor')->group(function () {
    Route::post('/courses', [InstructorCourseController::class, 'store']);
    Route::get('/courses', [InstructorCourseController::class, 'index']);
    Route::get('/courses/{course}', [InstructorCourseController::class, 'show']);
    Route::get('/dashboard/stats', [InstructorDashboardController::class, 'stats']);
    Route::get('/account', [InstructorAccountController::class, 'show']);
    Route::put('/account/password', [InstructorAccountController::class, 'updatePassword']);

    Route::get('/courses/{courseId}/quizzes', [InstructorQuizController::class, 'index']);
    Route::post('/courses/{courseId}/quizzes', [InstructorQuizController::class, 'store']);

    Route::get('/courses/{courseId}/quizzes/{quizId}/questions', [InstructorQuizQuestionController::class, 'index']);
    Route::post('/courses/{courseId}/quizzes/{quizId}/questions', [InstructorQuizQuestionController::class, 'store']);

    Route::put('/courses/{course}', [InstructorCourseController::class, 'update']);
    Route::post('/courses/{course}', [InstructorCourseController::class, 'update']);
    Route::get('/courses/{course}/lessons', [InstructorLessonController::class, 'index']);
    Route::post('/courses/{course}/lessons', [InstructorLessonController::class, 'store']);
});

// Public course display
Route::get('/coursesDisplay', [StudentDashboardController::class, 'coursesDisplay']);

// Student routes
Route::middleware('auth:sanctum')->prefix('student')->group(function () {
    // Course enrollment and progress
    Route::post('/enroll', [StudentDashboardController::class, 'enroll']);
    Route::get('/enrolled-courses', [StudentDashboardController::class, 'getEnrolledCourses']);
    Route::get('/course/{courseId}/lessons', [StudentDashboardController::class, 'getCourseLessons']);
    Route::get('/course/{courseId}/progress', [StudentDashboardController::class, 'getCourseProgress']);
    Route::post('/lesson/{lessonId}/complete', [StudentDashboardController::class, 'markLessonComplete']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'submitReview']);
    Route::get('/reviews/course/{courseId}/check', [ReviewController::class, 'hasUserReviewed']);
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'deleteReview']);

    // Quiz dashboard and attempts
    Route::get('/dashboard', [StudentQuizController::class, 'getDashboard']);
    Route::get('/all-attempts', [StudentQuizController::class, 'getAllAttempts']);

    // Course quizzes
    Route::get('/course/{courseId}/quizzes', [StudentQuizController::class, 'getCourseQuizzes']);

    // Quiz operations
    Route::get('/quiz/{quizId}/preview', [StudentQuizController::class, 'getQuizPreview']);
    Route::get('/quiz/{quizId}/start', [StudentQuizController::class, 'startQuiz']);
    Route::post('/quiz/{quizId}/submit', [StudentQuizController::class, 'submitQuiz']);

    // Quiz history
    Route::get('/quiz/{quizId}/history', [StudentQuizController::class, 'getAttemptHistory']);
    Route::get('/attempt/{attemptId}', [StudentQuizController::class, 'getAttemptDetails']);

    // ✅ FIXED: Certificate routes with SINGULAR parameter names
    Route::get('/certificates', [StudentCertificateController::class, 'index'])
        ->name('certificates.index');

    Route::post('/courses/{course}/certificates', [StudentCertificateController::class, 'generate'])
        ->name('certificates.generate');

    Route::get('/certificates/{certificate}', [StudentCertificateController::class, 'show'])
        ->name('certificates.show');

    Route::get('/certificates/{certificate}/download', [StudentCertificateController::class, 'download'])
        ->name('certificates.download');

    Route::get('/certificates/{certificate}/qr-code', [StudentCertificateController::class, 'downloadQrCode'])
        ->name('certificates.qr-code');
});

// Public routes
Route::get('/reviews/course/{courseId}', [ReviewController::class, 'getCourseReviews']);


Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard']);
    Route::get('/users', [AdminUsersController::class, 'index']);
    Route::put('/users/{user}', [AdminUsersController::class, 'update']); // change role / isActive
    // Courses
    Route::get('/courses', [AdminCoursesController::class, 'index']);
    Route::put('/courses/{course}', [AdminCoursesController::class, 'update']); // publish/unpublish
});


Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ]);
});
