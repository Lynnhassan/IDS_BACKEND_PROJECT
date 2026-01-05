<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [

        'email',
        'password',
        'fullName',
'role',
'isActive'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
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
