<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'SuperAdmin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $totalUsers = DB::table('users')->count();
        $totalStudents = DB::table('users')->where('role', 'Student')->count();
        $totalInstructors = DB::table('users')->where('role', 'Instructor')->count();

        $totalCourses = DB::table('courses')->count();
        $publishedCourses = DB::table('courses')->where('isPublished', 1)->count();
        $totalEnrollments = DB::table('enrollments')->count();
        $totalQuizzes = DB::table('quizzes')->count();

        $overallAvgScore = (float)(DB::table('quiz_attempts')->avg('score') ?? 0);

        $quizAverages = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quizId', '=', 'quizzes.id')
            ->select(
                'quizzes.id',
                'quizzes.title',
                DB::raw('AVG(quiz_attempts.score) as avgScore'),
                DB::raw('COUNT(quiz_attempts.id) as attempts')
            )
            ->groupBy('quizzes.id', 'quizzes.title');

        $topQuizzes = (clone $quizAverages)->orderByDesc('avgScore')->limit(5)->get();
        $lowQuizzes = (clone $quizAverages)->orderBy('avgScore')->limit(5)->get();

        $start = now()->subDays(6)->startOfDay();

        $labels7 = [];
        $users7 = array_fill(0, 7, 0);
        $courses7 = array_fill(0, 7, 0);
        $enrollments7 = array_fill(0, 7, 0);

        $usersByDay = DB::table('users')
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $coursesByDay = DB::table('courses')
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $enrollByDay = DB::table('enrollments')
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $labels7[] = $day->format('D');

            $d = $day->toDateString();
            $users7[$i] = (int)($usersByDay[$d] ?? 0);
            $courses7[$i] = (int)($coursesByDay[$d] ?? 0);
            $enrollments7[$i] = (int)($enrollByDay[$d] ?? 0);
        }

        return response()->json([
            'stats' => [
                'totalUsers' => $totalUsers,
                'students' => $totalStudents,
                'instructors' => $totalInstructors,
                'courses' => $totalCourses,
                'publishedCourses' => $publishedCourses,
                'enrollments' => $totalEnrollments,
                'quizzes' => $totalQuizzes,
                'overallAvgScore' => round($overallAvgScore, 1),
            ],
            'charts' => [
                'labels7' => $labels7,
                'users7' => $users7,
                'courses7' => $courses7,
                'enrollments7' => $enrollments7,
            ],
            'topQuizzes' => $topQuizzes,
            'lowQuizzes' => $lowQuizzes,
        ]);
    }
}
