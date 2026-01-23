<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quizId',
        'userId',
        'score',
        'attemptDate'
    ];

    // ✅ ADD THIS - Cast attemptDate to Carbon instance
    protected $casts = [
        'attemptDate' => 'datetime',
        'score' => 'float'
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quizId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
