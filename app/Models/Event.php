<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'event_code',
        'event_title',
        'date_start',
        'date_end',
        'year',
        'month',
        'time_start',
        'time_end',
        'classification',
        'collaboration',
        'status',
        'kv_path',
        'payment_unique_code',
        'is_attendance_open',
        'link_zoom'
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end'   => 'date',
        'is_attendance_open' => 'boolean',
    ];

    public function packets()
    {
        return $this->belongsToMany(Packet::class, 'event_packets')
        ->withPivot(['event_code', 'packet_name'])
        ->withTimestamps();;
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function speakers()
    {
        return $this->belongsToMany(
            Speaker::class,
            'event_speakers'
        )->withPivot(['event_code', 'speaker_code'])
        ->withTimestamps();
    }

    public function certificateTemplate()
    {
        return $this->hasOne(EventCertificate::class);
    }
}