<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonCompletion extends Model
{
    protected $fillable = [
        'lessonId',        // ✅ Changed
        'userId',          // ✅ Changed
        'completionDate'   // ✅ Added (if keeping this field)
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lessonId');
    }
}
