<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'title',
'content',
'videoUrl',
'estimatedDuration',
'courseId',
        'order'
    ];
    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId');
    }
    public function completions()
    {
        return $this->hasMany(LessonCompletion::class, 'lessonId');
    }

}
