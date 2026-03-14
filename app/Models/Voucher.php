<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_purchase',
        'quota',
        'used_count',
        'per_user_limit',
        'is_active',
        'start_at',
        'end_at',
        'stackable',
        'applies_to_all_users',
        'applies_to_all_items',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'quota' => 'integer',
        'used_count' => 'integer',
        'per_user_limit' => 'integer',
        'is_active' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'stackable' => 'boolean',
        'applies_to_all_users' => 'boolean',
        'applies_to_all_items' => 'boolean',
        'metadata' => 'array',
    ];

    /*
     * Users that are explicitly allowed to use this voucher.
     * pivot table holds max_usage, used_count, notes.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'voucher_user')
                    ->withPivot(['assigned_at', 'max_usage', 'used_count', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Raw pivot records describing item restrictions. Use VoucherItem model.
     */
    public function voucherItems()
    {
        return $this->hasMany(VoucherItem::class);
    }

    /**
     * Helper to fetch the actual models referenced by voucher_items. Returns a collection
     * of mixed models (courses, packages, etc). Used mainly for validation.
     */
    public function items()
    {
        return $this->voucherItems()->get()->map(function ($pivot) {
            if (! class_exists($pivot->item_type)) {
                return null;
            }
            return $pivot->item_type::find($pivot->item_id);
        })->filter();
    }

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class);
    }
}
