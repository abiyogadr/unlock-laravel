<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadProof extends Model
{
    protected $fillable = [
        'category',
        'file_path',
        'original_name',
        'registration_id',
        'registration_code',
    ];

    /**
     * Get the parent uploadable model (Registration, Payment, etc.)
     */

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
