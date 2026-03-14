<?php

namespace App\Models\Ecourse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class UserCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'course_id', 'subscription_id', 'current_module_id', 'enrolled_at', 'started_at',
        'completed_at', 'access_expires_at', 'acquisition_type',
        'progress', 'module_progress', 'completed_modules', 'total_modules', 'status'
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'access_expires_at' => 'datetime',
        'progress' => 'decimal:2'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function currentModule(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'current_module_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function userCourseModules(): HasMany
    {
        return $this->hasMany(UserCourseModule::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    // Accessors
    public function getProgressPercentageAttribute()
    {
        return $this->progress . '%';
    }

    public function getIsCompletedAttribute()
    {
        return $this->progress >= 100;
    }
}
