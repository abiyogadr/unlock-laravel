<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ShortLink extends Model
{
    protected $fillable = [
        'user_id',
        'short_code',
        'original_url',
        'click_count',
        'is_active',
        'expires_at',
        'last_clicked_at',
    ];

    protected $casts = [
        'click_count' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_clicked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(LinkClick::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    public function publicUrl(): string
    {
        return url('/u/' . $this->short_code);
    }

    public static function generateUniqueShortCode(?string $preferredCode = null, int $length = 6): string
    {
        $base = Str::of($preferredCode ?? '')
            ->lower()
            ->trim()
            ->replaceMatches('/[^a-z0-9_-]/', '')
            ->value();

        if ($base !== '') {
            $candidate = $base;
            $counter = 1;

            while (static::where('short_code', $candidate)->exists()) {
                $candidate = $base . '-' . $counter++;
            }

            return $candidate;
        }

        do {
            $candidate = Str::lower(Str::random($length));
        } while (static::where('short_code', $candidate)->exists());

        return $candidate;
    }
}