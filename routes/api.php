<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Test route
Route::get('/testt', function () {
    return response()->json(['message' => 'API works!']);
});

// Auth routes
Route::post('/signup', [AuthController::class, 'register']);
