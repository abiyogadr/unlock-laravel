<?php

namespace App\Models\Ecourse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'course_id', 'order_num', 'title', 'description',
        'video_path', 'module_type', 'pdf_path', 'duration_minutes', 'objectives'
    ];

    protected $casts = [
        'objectives' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($module) {
            $module->slug ??= Str::slug($module->title);
        });
    }

    // Route Model Binding
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ModuleMaterial::class);
    }

    public function userCourses()
    {
        return $this->hasMany(UserCourse::class, 'current_module_id');
    }

    // Accessors
    public function getPdfUrlAttribute()
    {
        if (!$this->pdf_path) return null;

        if (str_starts_with($this->pdf_path, 'http')) {
            return $this->pdf_path;
        }

        return asset('storage/' . $this->pdf_path);
    }

    public function getVideoUrlAttribute()
    {
        if (!$this->video_path) return null;
        
        if (str_starts_with($this->video_path, 'http')) {
            return $this->video_path;
        }
        
        return asset('storage/' . $this->video_path);
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

    // Scope
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId)->orderBy('order_num');
    }
}
