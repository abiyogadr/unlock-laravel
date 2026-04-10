<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkClick extends Model
{
    protected $fillable = [
        'landing_page_id',
        'landing_page_link_id',
        'ip_address',
        'user_agent',
        'referer',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(LandingPageLink::class, 'landing_page_link_id');
    }
}
