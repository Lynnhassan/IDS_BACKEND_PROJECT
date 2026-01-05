<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'courseId',
'title',
'passingScore',
'timeLimit',
'maxAttempts'
    ];
    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'quizId');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quizId');
    }

}
