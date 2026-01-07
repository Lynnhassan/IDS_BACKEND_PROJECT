<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstructorCourseController;
use App\Http\Controllers\InstructorLessonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorDashboardController;

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
