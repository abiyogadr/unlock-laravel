<?php

namespace App\Models\Ecourse;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'subscription_type',
        'plan_duration',
        'start_date',
        'end_date',
        'certificate_quota',
        'certificate_used',
        'ustar_total',
        'ustar_used',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CoursePackage::class);
    }

    public function userCourses(): HasMany
    {
        return $this->hasMany(UserCourse::class, 'subscription_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    public function getQuotaRemainingAttribute(): int
    {
        $remaining = ($this->quota_total ?? 0) - ($this->quota_used ?? 0);
        return $remaining > 0 ? $remaining : 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date !== null && $this->end_date->isPast();
    }
}
