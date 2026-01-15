<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packet extends Model
{
    protected $fillable = [
        'packet_name',
        'price',
        'description',
        'is_active',
        'requirements',
    ];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_packets')
        ->withPivot(['event_code', 'packet_name'])
        ->withTimestamps();
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    protected $casts = [
        'requirements' => 'array',
    ];
}
