<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    // ✅ CRITICAL: Explicitly set table name
    protected $table = 'certificates';

    // ✅ Make sure timestamps are enabled
    public $timestamps = true;

    protected $fillable = [
        'userId',
        'courseId',
        'verificationCode',
        'generatedDate'
    ];

    protected $casts = [
        'generatedDate' => 'datetime',
    ];

    // ✅ CRITICAL: Prevent automatic pluralization issues
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (!$certificate->verificationCode) {
                $certificate->verificationCode = strtoupper(Str::random(12));
            }
            if (!$certificate->generatedDate) {
                $certificate->generatedDate = now();
            }
        });
    }

    /**
     * Get the user who owns the certificate
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    /**
     * Get the course the certificate is for
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId', 'id');
    }
}
