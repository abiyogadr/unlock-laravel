<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class VoucherUser extends Pivot
{
    protected $table = 'voucher_user';

    protected $casts = [
        'assigned_at' => 'datetime',
        'max_usage' => 'integer',
        'used_count' => 'integer',
    ];
}
