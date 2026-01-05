<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable=[
        'courseId',
'userId',
'verificationCode',
        'generatedDate'
    ];
    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

}
