<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'courseId',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user who wrote the review
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId');
    }

    /**
     * Scope to get reviews by rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
}
