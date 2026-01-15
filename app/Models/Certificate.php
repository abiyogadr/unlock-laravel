<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'cert_id',
        'name',
        'event_id',
        'registration_id',
        'user_id',
        'event_code',
        'event_title',
        'event_subtitle',
        'speaker',
        'speaker_title',
        'date',
        'date_extra',
        'has_sign',
        'sign_path',
        'template',
        'packet',
        'publish_date',
    ];

    protected $casts = [
        'event_id' => 'integer',
        'publish_date' => 'date',
        'has_sign' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
