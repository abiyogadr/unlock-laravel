<?php

namespace App\Models\Ecourse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\Speaker;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'title', 'description', 'short_description',
        'thumbnail_path', 'duration_minutes', 'level', 'is_free',
        'price', 'credit_cost', 'access_duration_days', 'status', 'instructor_id', 'kv_path'
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'credit_cost' => 'integer',
        'access_duration_days' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            $course->slug ??= Str::slug($course->title);
        });

        static::updating(function ($course) {
            $course->slug ??= Str::slug($course->title);
        });
    }

    // Route Model Binding
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relationships
    public function speaker(): BelongsTo  // <-- Ganti nama method
    {
        return $this->belongsTo(Speaker::class, 'speaker_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            CourseCategory::class, 
            'category_course',           // nama tabel pivot
            'course_id',                 // kolom course di pivot ← EXPLICIT
            'category_id'                // kolom category di pivot ← EXPLICIT
        )->withTimestamps();
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order_num');
    }

    public function userCourses(): HasMany
    {
        return $this->hasMany(UserCourse::class);
    }

    public function courseCertificates(): HasMany
    {
        return $this->hasMany(\App\Models\Ecourse\CourseCertificate::class, 'course_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('categories', fn($q) => $q->where('id', $categoryId));
    }

    // Accessors
    public function getTotalDurationAttribute()
    {
        return $this->duration_minutes ?? $this->modules->sum('duration_minutes');
    }

    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail_path) {
            return asset('assets/images/course-placeholder.jpg');
        }

        if (str_starts_with($this->thumbnail_path, 'http')) {
            return $this->thumbnail_path;
        }

        return asset('storage/' . $this->thumbnail_path);
    }

    public function getKvUrlAttribute()
    {
        if (!$this->kv_path) {
            return asset('assets/images/course-placeholder.jpg');
        }

        if (str_starts_with($this->kv_path, 'http')) {
            return $this->kv_path;
        }

        return asset('storage/' . $this->kv_path);
    }

    public function getProgressAttribute()
    {
        return $this->userCourses()->where('user_id', auth()->id())->first()?->progress ?? 0;
    }

    public function getFormattedDurationAttribute()
    {
        $minutes = $this->duration_minutes;
        
        if ($minutes < 60) {
            return "{$minutes} menit";
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $remainingMinutes > 0 
            ? "{$hours} jam {$remainingMinutes} menit" 
            : "{$hours} jam";
    }

    public function getLevelLabelAttribute()
    {
        return match($this->level) {
            'pemula' => 'Pemula',
            'menengah' => 'Menengah',
            'lanjut' => 'Lanjutan',
            default => ucfirst($this->level)
        };
    }

}
