<?php

namespace App\Models\Ecourse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCertificate extends Model
{
    use HasFactory;

    protected $table = 'course_certificates';

    protected $fillable = [
        'certificate_number',
        'user_id',
        'course_id',
        'course_title',
        'user_name',
        'score',
        'issued_at',
        'pdf_path',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(\App\Models\Ecourse\Course::class, 'course_id');
    }

}
