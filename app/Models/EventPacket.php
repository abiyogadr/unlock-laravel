<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPacket extends Model
{
    protected $table = 'event_packets';

    protected $fillable = [
        'event_id',
        'packet_id',
        'event_code',
        'packet_name',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function packet()
    {
        return $this->belongsTo(Packet::class);
    }
}