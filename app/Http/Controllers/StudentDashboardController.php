<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Lesson;
use App\Models\LessonCompletion;
class StudentDashboardController extends Controller
{
    public function coursesDisplay()
    {
        $instructors = User::where('role', 'Instructor') // ✅ Fixed: Capitalized
        ->whereHas('coursesTaught', function ($query) {
            $query->where('isPublished', true);
        })
            ->with([
                'coursesTaught' => function ($query) {
                    $query->where('isPublished', true)
                        ->select('id', 'title', 'shortDescription', 'category', 'difficulty', 'instructorId');
                }
            ])
            ->select('id', 'fullName', 'email')
            ->get();

        return response()->json($instructors);
    }

    public function enroll(Request $request)
    {
        try {
            Log::info('Enroll attempt', [
                'user' => Auth::id(),
                'courseId' => $request->courseId
            ]);

            // Validate
            $validated = $request->validate([
                'courseId' => 'required|integer|exists:courses,id',
            ]);

            // Get authenticated user
            $user = Auth::user();

            if (!$user) {
                Log::error('User not authenticated');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            Log::info('User authenticated', ['userId' => $user->id]);

            // Check if course exists and is published
            $course = Course::where('id', $validated['courseId'])
                ->where('isPublished', true)
                ->first();

            if (!$course) {
                Log::warning('Course not found or unpublished', ['courseId' => $validated['courseId']]);
                return response()->json(['error' => 'Course not found or unpublished'], 404);
            }

            Log::info('Course found', ['courseId' => $course->id]);

            // Check if already enrolled
            $existing = Enrollment::where('userId', $user->id)
                ->where('courseId', $course->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Already enrolled',
                    'enrollment' => $existing
                ], 200);
            }
            // Create enrollment
            $enrollment = Enrollment::create([
                'userId' => $user->id,
                'courseId' => $course->id,
            ]);

            Log::info('Enrollment created', ['enrollmentId' => $enrollment->id]);

            return response()->json([
                'message' => 'Enrolled successfully',
                'enrollment' => $enrollment
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Enrollment exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }


    /**
     * Get all enrolled courses for the authenticated student with progress
     */
    public function getEnrolledCourses(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $enrolledCourses = Enrollment::where('userId', $user->id)
                ->with([
                    'course' => function ($query) use ($user) {
                        $query->with(['instructor:id,fullName'])
                            ->withCount([
                                'lessons as total_lessons_count',
                                'lessons as completed_lessons_count' => function ($q) use ($user) {
                                    $q->whereHas('completions', function ($completion) use ($user) {
                                        $completion->where('userId', $user->id);
                                    });
                                }
                            ]);
                    }
                ])
                ->get()
                ->map(function ($enrollment) use ($user) {
                    $course = $enrollment->course;

                    if (!$course) {
                        return null;
                    }

                    $totalLessons = $course->total_lessons_count ?? 0;
                    $completedLessons = $course->completed_lessons_count ?? 0;

                    $progress = $totalLessons > 0
                        ? round(($completedLessons / $totalLessons) * 100)
                        : 0;

                    // Get next incomplete lesson
                    $nextLesson = Lesson::where('courseId', $course->id)
                        ->whereDoesntHave('completions', function ($query) use ($user) {
                            $query->where('userId', $user->id);
                        })
                        ->orderBy('order')
                        ->first();

                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'instructor' => $course->instructor->fullName ?? 'Unknown',
                        'category' => $course->category,
                        'difficulty' => $course->difficulty,
                        'shortDescription' => $course->shortDescription,
                        'thumbnail' => $course->thumbnail ?? '📚',
                        'progress' =>(int) $progress,
                        'totalLessons' => $totalLessons,
                        'completedLessons' => $completedLessons,
                        'enrolledDate' => $enrollment->created_at->format('Y-m-d'),
                        'status' => $progress >= 100 ? 'completed' : 'in-progress',
                        'nextLesson' => $nextLesson ? $nextLesson->title : null
                    ];
                })
                ->filter(); // Remove null values

            return response()->json([
                'success' => true,
                'courses' => $enrolledCourses->values() // Re-index array
            ]);

        } catch (\Exception $e) {
            Log::error('Get enrolled courses error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to fetch enrolled courses',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a lesson as complete for the authenticated student
     */
    public function markLessonComplete(Request $request, $lessonId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Validate lesson exists
            $lesson = Lesson::findOrFail($lessonId);

            // Check if user is enrolled in the course
            $isEnrolled = Enrollment::where('userId', $user->id)
                ->where('courseId', $lesson->courseId)
                ->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'error' => 'You must be enrolled in this course to mark lessons complete'
                ], 403);
            }

            // Check if already completed
            $existing = LessonCompletion::where('userId', $user->id)
                ->where('lessonId', $lessonId)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lesson already completed',
                    'completion' => $existing
                ], 200);
            }

            // Mark as complete
            $completion = LessonCompletion::create([
                'userId' => $user->id,
                'lessonId' => $lessonId,
                'completionDate' => now()
            ]);

            // Get updated course progress
            $course = Course::where('id', $lesson->courseId)
                ->withCount([
                    'lessons as total_lessons_count',
                    'lessons as completed_lessons_count' => function ($q) use ($user) {
                        $q->whereHas('completions', function ($completion) use ($user) {
                            $completion->where('userId', $user->id);
                        });
                    }
                ])
                ->first();

            $progress = $course->total_lessons_count > 0
                ? round(($course->completed_lessons_count / $course->total_lessons_count) * 100)
                : 0;

            Log::info('Lesson marked complete', [
                'userId' => $user->id,
                'lessonId' => $lessonId,
                'courseId' => $lesson->courseId,
                'progress' => $progress
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson marked as complete',
                'completion' => $completion,
                'courseProgress' => [
                    'totalLessons' => $course->total_lessons_count,
                    'completedLessons' => $course->completed_lessons_count,
                    'progress' => $progress
                ]
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Lesson not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Mark lesson complete error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to mark lesson as complete',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get course progress for a specific course
     */
    public function getCourseProgress(Request $request, $courseId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Check if enrolled
            $enrollment = Enrollment::where('userId', $user->id)
                ->where('courseId', $courseId)
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'error' => 'Not enrolled in this course'
                ], 404);
            }

            // Get course with progress
            $course = Course::where('id', $courseId)
                ->with(['instructor:id,fullName'])
                ->withCount([
                    'lessons as total_lessons_count',
                    'lessons as completed_lessons_count' => function ($q) use ($user) {
                        $q->whereHas('completions', function ($completion) use ($user) {
                            $completion->where('userId', $user->id);
                        });
                    }
                ])
                ->first();

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            $totalLessons = $course->total_lessons_count;
            $completedLessons = $course->completed_lessons_count;
            $progress = $totalLessons > 0
                ? round(($completedLessons / $totalLessons) * 100)
                : 0;

            // Get all lessons with completion status
            $lessons = Lesson::where('courseId', $courseId)
                ->orderBy('order')
                ->get()
                ->map(function ($lesson) use ($user) {
                    $isCompleted = LessonCompletion::where('userId', $user->id)
                        ->where('lessonId', $lesson->id)
                        ->exists();

                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'order' => $lesson->order,
                        'estimatedDuration' => $lesson->estimatedDuration,
                        'isCompleted' => $isCompleted
                    ];
                });

            return response()->json([
                'success' => true,
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'instructor' => $course->instructor->fullName ?? 'Unknown',
                    'category' => $course->category,
                    'difficulty' => $course->difficulty,
                ],
                'progress' => [
                    'percentage' => $progress,
                    'totalLessons' => $totalLessons,
                    'completedLessons' => $completedLessons,
                    'status' => $progress === 100 ? 'completed' : 'in-progress'
                ],
                'lessons' => $lessons
            ]);

        } catch (\Exception $e) {
            Log::error('Get course progress error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch course progress',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getCourseLessons(Request $request, $courseId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Check if enrolled
            $isEnrolled = Enrollment::where('userId', $user->id)
                ->where('courseId', $courseId)
                ->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'error' => 'Not enrolled in this course'
                ], 403);
            }

            // Get lessons with completion status
            $lessons = Lesson::where('courseId', $courseId)
                ->orderBy('order')
                ->get()
                ->map(function ($lesson) use ($user) {
                    $isCompleted = LessonCompletion::where('userId', $user->id)
                        ->where('lessonId', $lesson->id)
                        ->exists();

                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'content' => $lesson->content,
                        'videoUrl' => $lesson->videoUrl,
                        'estimatedDuration' => $lesson->estimatedDuration,
                        'order' => $lesson->order,
                        'isCompleted' => $isCompleted
                    ];
                });

            return response()->json([
                'success' => true,
                'lessons' => $lessons
            ]);

        } catch (\Exception $e) {
            Log::error('Get course lessons error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch lessons',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
