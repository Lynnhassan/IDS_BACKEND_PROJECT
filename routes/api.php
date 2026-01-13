<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstructorCourseController;
use App\Http\Controllers\InstructorLessonController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorDashboardController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Auth;
// Quick test route
Route::get('/testt', function () {
    return response()->json(['message' => 'API works!']);
});

Route::post('/signup', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->prefix('instructor')->group(function () {
    Route::post('/courses', [InstructorCourseController::class, 'store']);
    Route::get('/courses', [InstructorCourseController::class, 'index']);
    Route::get('/courses/{course}', [InstructorCourseController::class, 'show']);
    Route::get('/dashboard/stats', [InstructorDashboardController::class, 'stats']);

    Route::put('/courses/{course}', [InstructorCourseController::class, 'update']);
    Route::patch('/courses/{course}', [InstructorCourseController::class, 'update']);
    Route::get('/courses/{course}/lessons', [InstructorLessonController::class, 'index']);
    Route::post('/courses/{course}/lessons', [InstructorLessonController::class, 'store']);
});
//Route::middleware('auth:sanctum')->get('/student/test', function () {
//    return response()->json([
//        'user' => Auth::user()
//    ]);
//});
Route::get('/coursesDisplay', [StudentDashboardController::class, 'coursesDisplay']);
Route::middleware('auth:sanctum')->prefix('student')->group(function () {
    Route::post('/enroll', [StudentDashboardController::class, 'enroll']);
    Route::get('/enrolled-courses', [StudentDashboardController::class, 'getEnrolledCourses']);
    Route::get('/course/{courseId}/lessons', [StudentDashboardController::class, 'getCourseLessons']);
    Route::get('/course/{courseId}/progress', [StudentDashboardController::class, 'getCourseProgress']);
    Route::post('/lesson/{lessonId}/complete', [StudentDashboardController::class, 'markLessonComplete']);
    Route::post('/reviews', [ReviewController::class, 'submitReview']);
    Route::get('/reviews/course/{courseId}/check', [ReviewController::class, 'hasUserReviewed']);
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'deleteReview']);
});
Route::get('/reviews/course/{courseId}', [ReviewController::class, 'getCourseReviews']);
