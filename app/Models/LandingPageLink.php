<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPageLink extends Model
{
    protected $fillable = [
        'landing_page_id',
        'type',
        'label',
        'label_2',
        'url',
        'url_2',
        'icon',
        'thumbnail',
        'content',
        'image_path',
        'elem_style',
        'sort_order',
        'is_active',
        'opens_in_new_tab',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'opens_in_new_tab'=> 'boolean',
        'sort_order'      => 'integer',
        'elem_style'      => 'array',
    ];

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(LinkClick::class);
    }
}
