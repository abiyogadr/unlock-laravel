<?php

namespace App\Models\Ecourse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class CourseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'color',
        'sort_order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug ??= Str::slug($category->name);
        });

        static::updating(function ($category) {
            $category->slug ??= Str::slug($category->name);
        });
    }

    // Route Model Binding
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relationships
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(
            Course::class, 
            'category_course',           // nama tabel pivot
            'category_id',               // kolom category di pivot ← EXPLICIT
            'course_id'                  // kolom course di pivot ← EXPLICIT
        )->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeWithCourses($query, $limit = 4)
    {
        return $query->with(['courses' => fn($q) => $q->published()->limit($limit)]);
    }

    // Accessors
    public function getIconClassAttribute()
    {
        return 'fas ' . $this->icon;
    }
}
