<?php

namespace App\Models\Ecourse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_module_id', 'title', 'type', 'file_path',
        'file_size', 'description'
    ];

    // Relationships
    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class);
    }

    // Accessors
    public function getIconAttribute()
    {
        return match($this->type) {
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'ppt' => 'fa-file-powerpoint',
            'zip' => 'fa-file-archive',
            default => 'fa-file'
        };
    }

    public function getFileUrlAttribute()
    {
        if (str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }
        return asset('storage/' . $this->file_path);
    }

    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'pdf' => 'text-red-500',
            'doc' => 'text-blue-500',
            'ppt' => 'text-orange-500',
            'zip' => 'text-green-500',
            default => 'text-gray-500'
        };
    }
}
