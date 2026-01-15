<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCertificate extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal.
     */
    protected $fillable = [
        'event_id',
        'event_title',
        'event_subtitle',
        'speaker',
        'speaker_title',
        'date_string',
        'date_extra',
        'template',
        'has_sign',
        'sign_path',
        'temp_sign_path',
    ];

    /**
     * Casting data.
     */
    protected $casts = [
        'event_id' => 'integer',
        // Jika has_sign berisi '1'/'0' atau string 'true'/'false'
        'has_sign' => 'boolean', 
    ];

    /**
     * Relasi Kebalikan ke Event.
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
