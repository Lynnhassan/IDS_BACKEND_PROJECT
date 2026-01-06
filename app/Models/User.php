<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'fullName',
        'email',
        'password',
        'role',
        'isActive',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Laravel auto-hashes
            'isActive' => 'boolean',
        ];
    }

    // ================= RELATIONSHIPS =================

    public function coursesTaught()
    {
        return $this->hasMany(Course::class, 'instructorId');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'userId');
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'userId');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'userId');
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(
            Course::class,
            'enrollments',
            'userId',
            'courseId'
        );
    }
}
