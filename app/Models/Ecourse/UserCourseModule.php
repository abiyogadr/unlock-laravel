<?php

namespace App\Models\Ecourse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_course_id', 'course_module_id', 'progress',
        'started_at', 'completed_at', 'is_completed'
    ];

    protected $casts = [
        'progress' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean'
    ];

    // Relationships
    public function userCourse(): BelongsTo
    {
        return $this->belongsTo(UserCourse::class, 'user_course_id');
    }

    public function courseModule(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class);
    }

    // Scopes
    public function scopeForUserCourse($query, $userCourseId)
    {
        return $query->where('user_course_id', $userCourseId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }
}
