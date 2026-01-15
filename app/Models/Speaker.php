<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
