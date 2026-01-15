<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Registration extends Model
{
    protected $fillable = [
        'event_id',
        'packet_id',
        'user_id',
        'registration_code',
        'event_code',
        'event_name',
        'packet_name',
        'name',
        'email',
        'whatsapp',
        'gender',
        'age',
        'province',
        'city',
        'profession',
        'channel_information',
        'price',
        'registration_status',
        'payment',
        'payment_status',
        'paid_at',
        'snap_token',
        'flag_sub',
        'screenshot_follow_ig',
        'screenshot_follow_tiktok',
        'is_attended',
        'attended_at', 
        'attendance_proof',
        'rating',
        'feedback',
        'next_theme_suggestion'
    ];

    protected $casts = [
        'channel_information' => 'array',
        'feedback' => 'array',
        'is_attended' => 'boolean',
        'attended_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function packet()
    {
        return $this->belongsTo(Packet::class);
    }

    public function uploadProofs()
    {
        return $this->hasMany(UploadProof::class);
    }

    public static function generateRegistrationCode($event)
    {
        $eventCodeParts = explode('-', $event->event_code);
        $eventCodeSuffix = strtoupper(end($eventCodeParts));
        
        do {
            $uniqueCode = strtoupper(Str::random(5));
            $registrationCode = 'REG-UNL' . $eventCodeSuffix . '-' . $uniqueCode;
        } while (static::where('registration_code', $registrationCode)->exists());
        
        return $registrationCode;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'registration_id', 'id');
    }

}
