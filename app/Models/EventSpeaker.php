<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSpeaker extends Model
{
    protected $table = 'event_speakers';

    protected $fillable = [
        'event_id',
        'speaker_id',
        'event_code',
        'speaker_code',
        'speaker_name',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function speaker()
    {
        return $this->belongsTo(Speaker::class);
    }
}
