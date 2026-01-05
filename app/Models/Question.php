<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'quizId',
'questionText',
'questionType'
    ];
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quizId');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'questionId');
    }

}
