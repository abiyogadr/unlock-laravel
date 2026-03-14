<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VoucherItem extends Model
{
    protected $table = 'voucher_items';

    protected $fillable = [
        'voucher_id',
        'item_type',
        'item_id',
        'rule_type',
    ];

    protected $casts = [
        'rule_type' => 'string',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
