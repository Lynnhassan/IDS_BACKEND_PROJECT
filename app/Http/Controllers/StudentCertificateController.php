<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class StudentCertificateController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $certificates = Certificate::where('userId', $user->id)
                ->with(['course'])
                ->orderBy('generatedDate', 'desc')
                ->get()
                ->map(function ($certificate) {
                    return [
                        'id' => $certificate->id,
                        'verification_code' => $certificate->verificationCode,
                        'generated_at' => $certificate->generatedDate->format('Y-m-d H:i:s'),
                        'course' => [
                            'id' => $certificate->course->id,
                            'title' => $certificate->course->title ?? 'N/A',
                            'category' => $certificate->course->category ?? 'N/A',
                        ]
                    ];
                });

            return response()->json([
                'certificates' => $certificates
            ]);

        } catch (\Exception $e) {
            \Log::error('Certificates index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch certificates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generate(Course $course)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // ✅ OPTION 1: Simple - allow anyone to generate (for testing)
            // Remove the check below if you want strict validation

            // ✅ OPTION 2: Strict - check if course is completed
            // if (!$this->hasCompletedCourse($user, $course)) {
            //     return response()->json(['message' => 'You must complete the course first.'], 403);
            // }

            $certificate = Certificate::firstOrCreate(
                [
                    'userId' => $user->id,
                    'courseId' => $course->id,
                ]
            );

            return response()->json([
                'message' => 'Certificate generated successfully',
                'certificate_id' => $certificate->id,
                'verification_code' => $certificate->verificationCode
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Certificate generation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Certificate $certificate)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            if ($certificate->userId !== $user->id && $user->role !== 'SuperAdmin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $certificate->load(['user', 'course.instructor']);

            return response()->json([
                'certificate' => [
                    'id' => $certificate->id,
                    'user_id' => $certificate->userId,
                    'course_id' => $certificate->courseId,
                    'verification_code' => $certificate->verificationCode,
                    'generated_at' => $certificate->generatedDate->format('Y-m-d H:i:s'),
                    'user' => [
                        'id' => $certificate->user->id,
                        'full_name' => $certificate->user->fullName ?? 'N/A',
                        'email' => $certificate->user->email,
                    ],
                    'course' => [
                        'id' => $certificate->course->id,
                        'title' => $certificate->course->title ?? 'N/A',
                        'category' => $certificate->course->category ?? 'N/A',
                    ],
                ],
                'verification_url' => route('certificates.verify.code', $certificate->verificationCode)
            ]);

        } catch (\Exception $e) {
            \Log::error('Certificate show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function download(Certificate $certificate)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // ✅ FIXED: Clean authorization check
            if ($certificate->userId !== $user->id && $user->role !== 'SuperAdmin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Load relationships
            $certificate->load(['user', 'course.instructor']);

            // Validate data
            if (!$certificate->user || !$certificate->course) {
                \Log::error('Certificate data incomplete:', [
                    'certificate_id' => $certificate->id,
                    'user_exists' => $certificate->user ? 'YES' : 'NO',
                    'course_exists' => $certificate->course ? 'YES' : 'NO',
                ]);

                return response()->json([
                    'message' => 'Certificate data incomplete',
                ], 500);
            }

            // Generate QR code
            $qrCode = null;
            try {
                $options = new QROptions([
                    'version'      => 5,
                    'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel'     => QRCode::ECC_L,
                    'scale'        => 5,
                    'imageBase64'  => true,
                ]);

                $qrcode = new QRCode($options);
                $qrCode = $qrcode->render(route('certificates.verify.code', $certificate->verificationCode));
            } catch (\Exception $e) {
                \Log::warning('QR code generation failed: ' . $e->getMessage());
            }

            // Generate PDF
            $pdf = Pdf::loadView('certificates.pdf', compact('certificate', 'qrCode'))
                ->setPaper('a4', 'landscape');

            // Return PDF
            return response($pdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="certificate-' . $certificate->verificationCode . '.pdf"');

        } catch (\Exception $e) {
            \Log::error('Certificate download error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'message' => 'Failed to download certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadQrCode(Certificate $certificate)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            if ($certificate->userId !== $user->id && $user->role !== 'SuperAdmin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $verificationUrl = route('certificates.verify.code', $certificate->verificationCode);

            $options = new QROptions([
                'version'      => 5,
                'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'     => QRCode::ECC_L,
                'scale'        => 10,
                'imageBase64'  => false,
            ]);

            $qrcode = new QRCode($options);
            $qrImage = $qrcode->render($verificationUrl);

            return response($qrImage)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="qr-' . $certificate->verificationCode . '.png"');

        } catch (\Exception $e) {
            \Log::error('QR download error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to generate QR code',
                'error' => $e->getMessage()
            ], 500);
        }
    }



// ✅ UPDATED verifyByCode method for StudentCertificateController
// Replace the existing verifyByCode method with this one

    public function verifyByCode($verificationCode)
    {
        try {
            $certificate = Certificate::where('verificationCode', $verificationCode)
                ->with(['user', 'course.instructor'])
                ->first();

            // ❗ Handle invalid code
            if (!$certificate) {
                return view('certificates.verify', [
                    'valid' => false,
                    'certificate' => null
                ]);
            }

            $certificateData = [
                'student_name'      => $certificate->user->fullName ?? 'N/A',
                'course_title'      => $certificate->course->title ?? 'N/A',
                'category'          => $certificate->course->category ?? 'N/A',
                'difficulty'        => $certificate->course->difficulty ?? 'N/A',
                'issued_date'       => $certificate->generatedDate->format('F d, Y'),
                'verification_code' => $certificate->verificationCode,
                'instructor'        => $certificate->course->instructor->fullName ?? 'N/A',
            ];

            // JSON for API / Postman
            if (request()->wantsJson()) {
                return response()->json([
                    'valid' => true,
                    'certificate' => $certificateData
                ]);
            }

            // HTML for browser / QR scan
            return view('certificates.verify', [
                'valid' => true,
                'certificate' => $certificateData
            ]);

        } catch (\Exception $e) {
            \Log::error('Certificate verify error: ' . $e->getMessage());

            return view('certificates.verify', [
                'valid' => false,
                'certificate' => null
            ]);
        }
    }

//    public function verifyByCode($verificationCode)
//    {
//        try {
//            $certificate = Certificate::where('verificationCode', $verificationCode)
//                ->with(['user', 'course.instructor'])
//                ->first();
//
//            if (!$certificate) {
//                return response()->json([
//                    'message' => 'Certificate not found'
//                ], 404);
//            }
//
//            return response()->json([
//                'valid' => true,
//                'certificate' => [
//                    'student_name' => $certificate->user->fullName ?? 'N/A',
//                    'course_title' => $certificate->course->title ?? 'N/A',
//                    'category' => $certificate->course->category ?? 'N/A',
//                    'difficulty' => $certificate->course->difficulty ?? 'N/A',
//                    'issued_date' => $certificate->generatedDate->format('F d, Y'),
//                    'verification_code' => $certificate->verificationCode,
//                    'instructor' => $certificate->course->instructor->fullName ?? 'N/A'
//                ]
//            ]);
//
//        } catch (\Exception $e) {
//            \Log::error('Certificate verify error: ' . $e->getMessage());
//            return response()->json([
//                'message' => 'Failed to verify certificate',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }

    /**
     * Check if user has completed the course
     *
     * ⚠️ CURRENTLY DISABLED - Returns true for all users
     * Uncomment the validation logic below if you want strict course completion checks
     */
    private function hasCompletedCourse($user, $course): bool
    {
        // ✅ OPTION 1: Simple - allow all (for testing)
        return true;

        // ✅ OPTION 2: Strict validation (uncomment to enable)
        /*
        // 1. Check if user is enrolled
        $enrollment = $user->enrollments()->where('courseId', $course->id)->first();
        if (!$enrollment) {
            return false;
        }

        // 2. Check if ALL lessons are completed
        $totalLessons = $course->lessons()->count();
        if ($totalLessons === 0) {
            return false; // Course must have lessons
        }

        $completedLessons = \DB::table('lesson_progress')
            ->where('userId', $user->id)
            ->whereIn('lessonId', $course->lessons()->pluck('id'))
            ->where('isCompleted', true)
            ->count();

        if ($completedLessons < $totalLessons) {
            return false;
        }

        // 3. Check if user has at least one quiz with 90%+ score for this course
        $hasPassed = \DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quizId', '=', 'quizzes.id')
            ->where('quiz_attempts.userId', $user->id)
            ->where('quizzes.courseId', $course->id)
            ->where('quiz_attempts.score', '>=', 90)
            ->exists();

        if (!$hasPassed) {
            return false;
        }

        return true;
        */
    }
}
