<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/test', function() {
    return response()->json(['message' => 'API works!']);
});

Route::get('/', function () {
    return view('welcome');
});
