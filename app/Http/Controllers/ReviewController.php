<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a course
     */
    public function getCourseReviews($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);

            $reviews = Review::where('courseId', $courseId)
                ->where('isApproved', true)
                ->with('user:id,fullName')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'userName' => $review->user->fullName ?? 'Anonymous',
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'createdAt' => $review->created_at->format('M d, Y'),
                        'timeAgo' => $review->created_at->diffForHumans()
                    ];
                });

            $averageRating = $course->averageRating() ?? 0;
            $totalReviews = $course->reviewCount() ?? 0;

            return response()->json([
                'success' => true,
                'reviews' => $reviews,
                'averageRating' => round($averageRating, 1),
                'totalReviews' => $totalReviews
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch reviews',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit a review
     */
    public function submitReview(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'courseId' => 'required|exists:courses,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user is enrolled in the course
            $enrollment = Enrollment::where('userId', $user->id)
                ->where('courseId', $request->courseId)
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'error' => 'You must be enrolled in this course to leave a review'
                ], 403);
            }

            // Check if user already reviewed this course
            $existingReview = Review::where('userId', $user->id)
                ->where('courseId', $request->courseId)
                ->first();

            if ($existingReview) {
                // Update existing review
                $existingReview->update([
                    'rating' => $request->rating,
                    'comment' => $request->comment
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Review updated successfully',
                    'review' => $existingReview
                ]);
            }

            // Create new review
            $review = Review::create([
                'userId' => $user->id,
                'courseId' => $request->courseId,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'isApproved' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'review' => $review
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to submit review',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has reviewed a course
     */
    public function hasUserReviewed(Request $request, $courseId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $review = Review::where('userId', $user->id)
                ->where('courseId', $courseId)
                ->first();

            return response()->json([
                'success' => true,
                'hasReviewed' => $review !== null,
                'review' => $review
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to check review status'
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function deleteReview($reviewId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $review = Review::findOrFail($reviewId);

            // Only the review author can delete
            if ($review->userId !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'You can only delete your own reviews'
                ], 403);
            }

            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete review'
            ], 500);
        }
    }
}
