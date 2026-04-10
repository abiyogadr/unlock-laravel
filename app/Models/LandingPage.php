<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    public const DEFAULT_STYLE = [
        'bg_type'           => 'solid',
        'bg_color'          => '#ffffff',
        'bg_gradient_from'  => '#667eea',
        'bg_gradient_to'    => '#764ba2',
        'font_family'       => 'sans',
        'page_max_width'    => 'sm',
        'title_align'       => 'center',
        'title_size'        => '2xl',
        'title_color'       => '#1f2937',
        'title_bold'        => true,
        'bio_align'         => 'center',
        'bio_size'          => 'sm',
        'bio_color'         => '#6b7280',
        'bio_bold'          => false,
        'avatar_size'       => 'md',
        'avatar_rounded'    => 'full',
        'use_avatar'        => true,
        'use_cover'         => true,
    ];

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'title',
        'bio',
        'avatar',
        'cover_image',
        'template_type',
        'style',
        'status',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'style'    => 'array',
    ];

    public function getResolvedStyleAttribute(): array
    {
        return array_merge(self::DEFAULT_STYLE, $this->style ?? []);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(LandingPageLink::class)->orderBy('sort_order');
    }

    public function activeLinks(): HasMany
    {
        return $this->hasMany(LandingPageLink::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(LinkClick::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->is_active;
    }

    public function getPublicUrl(): string
    {
        return url("/p/{$this->slug}");
    }

    public static function generateUniqueSlug(string $text, ?int $excludeId = null): string
    {
        $slug = Str::slug($text);
        $original = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }
}
