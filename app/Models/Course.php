<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'shortDescription',
        'longDescription',
        'category',
        'difficulty',
        'thumbnail',
        'pdf', //added here also
        'instructorId',
'isPublished'
    ];
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructorId');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'courseId');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'courseId');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'courseId');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'courseId');
    }
    /**
     * Get all reviews for this course
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'courseId');
    }

    /**
     * Get approved reviews only
     */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class, 'courseId')->where('isApproved', true);
    }

    /**
     * Get average rating
     */
    public function averageRating()
    {
        return $this->reviews()->avg('rating');  // Remove ->where('isApproved', true)
    }

    public function reviewCount()
    {
        return $this->reviews()->count();  // Remove ->where('isApproved', true)
    }


}
