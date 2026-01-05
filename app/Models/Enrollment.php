<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'courseId',
'userId'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId');
    }

}
