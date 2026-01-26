<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentCertificateController;

Route::get('/test', function() {
    return response()->json(['message' => 'API works!']);
});

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-pdf-view', function () {
    if (!view()->exists('certificates.pdf')) {
        return 'View NOT found at: ' . resource_path('views/certificates/pdf.blade.php');
    }

    // Test with fake data
    $certificate = new stdClass();
    $certificate->user = new stdClass();
    $certificate->user->fullName = 'Test Student';
    $certificate->course = new stdClass();
    $certificate->course->title = 'Test Course';
    $certificate->course->category = 'Programming';
    $certificate->generatedDate = now();
    $certificate->verificationCode = 'TEST123';
$certificate->course->difficulty="Easy";
    // ✅ Instructor is required!
    $certificate->course->instructor = new stdClass();
    $certificate->course->instructor->fullName = 'Dr. Jane Smith';
    $qrCode = null;

    return view('certificates.pdf', compact('certificate', 'qrCode'));
});

// Public certificate verification
Route::get('/verify/{verificationCode}', [StudentCertificateController::class, 'verifyByCode'])
    ->name('certificates.verify.code');
