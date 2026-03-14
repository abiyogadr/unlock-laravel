<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ecourse\Course;

class Speaker extends Model
{
    protected $fillable = [
        'speaker_code',
        'speaker_name',
        'prefix_title',
        'suffix_title',
        'email',
        'phone',
        'position',
        'company',
    ];

    public function events()
    {
        return $this->belongsToMany(
            Event::class,
            'event_speakers'
        )->withPivot(['event_code', 'speaker_code'])
        ->withTimestamps();
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'speaker_id');
    }

    public function ecourses()
    {
        return $this->hasOne(Course::class, 'speaker_id');
    }
}
