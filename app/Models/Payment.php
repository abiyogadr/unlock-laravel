<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'registration_id',
        'registration_code',
        'transaction_status',
        'fraud_status',
        'registration_status',
        'payment_type',
        'transaction_id',
        'gross_amount',
        'transaction_time',
        'settlement_time',
        'paid_at',
        'raw_callback',
    ];

    protected $casts = [
        'transaction_time' => 'datetime',
        'settlement_time'  => 'datetime',
        'paid_at'          => 'datetime',
        'raw_callback'     => 'array',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
