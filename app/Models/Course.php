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

}
