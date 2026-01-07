<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstructorDashboardController extends Controller
{
    public function stats()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $courseIds = Course::where('instructorId', $user->id)->pluck('id');

        $coursesCount = $courseIds->count();
        $lessonsCount = Lesson::whereIn('courseId', $courseIds)->count();

        // simple students count (all students). Later we can count enrolled.
        $studentsCount = User::where('role', 'Student')->count();

        // ---------- CHART DATA ----------
        // Courses created per day (last 7 days)
        $coursesPerDay = Course::select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->where('instructorId', $user->id)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Lessons created per day (last 7 days)
        $lessonsPerDay = Lesson::select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->whereIn('courseId', $courseIds)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Lesson completions per day (last 7 days)
        $completionsPerDay = LessonCompletion::select(DB::raw('DATE(completionDate) as day'), DB::raw('COUNT(*) as total'))
            ->whereIn('lessonId', function ($q) use ($courseIds) {
                $q->select('id')->from('lessons')->whereIn('courseId', $courseIds);
            })
            ->where('completionDate', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // make 7-day labels + fill missing days with 0
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = now()->subDays($i)->format('D'); // Mon Tue ...
        }

        $mapTo7 = function ($rows) {
            $byDay = [];
            foreach ($rows as $r) $byDay[$r->day] = (int)$r->total;

            $out = [];
            for ($i = 6; $i >= 0; $i--) {
                $key = now()->subDays($i)->format('Y-m-d');
                $out[] = $byDay[$key] ?? 0;
            }
            return $out;
        };

        return response()->json([
            "stats" => [
                "courses" => $coursesCount,
                "students" => $studentsCount,
                "lessons" => $lessonsCount,
            ],
            "charts" => [
                "labels7" => $labels,
                "courses7" => $mapTo7($coursesPerDay),
                "lessons7" => $mapTo7($lessonsPerDay),
                "completions7" => $mapTo7($completionsPerDay),
            ],
        ]);
    }
}
