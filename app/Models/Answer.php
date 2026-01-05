<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'questionId',
'answerText',
'isCorrect'
    ];
    public function question()
    {
        return $this->belongsTo(Question::class, 'questionId');
    }

}
