<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VoucherService
{
    /**
     * Resolve the payment context based on query parameters.
     * Array keys: type, course, package, item_type, item_id, subtotal
     *
     * @param array $query
     * @return array
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function resolvePaymentContext(array $query): array
    {
        $type = $query['type'] ?? null;
        $course = null;
        $package = null;
        $subtotal = 0;
        $itemType = null;
        $itemId = null;

        if ($type === 'course') {
            $course = \App\Models\Ecourse\Course::where('slug', $query['course_slug'])->firstOrFail();
            $subtotal = $course->price;
            $itemType = \App\Models\Ecourse\Course::class;
            $itemId = $course->id;
            if (!empty($query['package_id'])) {
                $package = \App\Models\Ecourse\CoursePackage::findOrFail($query['package_id']);
                // ensure package belongs to course if relation exists
                if (isset($package->course_id) && $package->course_id != $course->id) {
                    throw new ModelNotFoundException('Package does not belong to course');
                }
                // use package price, but itemType stays as Course (type=course means direct course purchase)
                $subtotal = $package->discount_price ?: $package->price;
            }
        } elseif ($type === 'package') {
            $package = \App\Models\Ecourse\CoursePackage::findOrFail($query['package_id']);
            $subtotal = $package->discount_price ?: $package->price;
            $itemType = \App\Models\Ecourse\CoursePackage::class;
            $itemId = $package->id;
            // we may optionally resolve course
            if (!empty($query['course_slug'])) {
                $course = \App\Models\Ecourse\Course::where('slug', $query['course_slug'])->first();
            }
        } else {
            throw new \InvalidArgumentException('Unsupported payment type');
        }

        return compact('type', 'course', 'package', 'itemType', 'itemId', 'subtotal');
    }

    /**
     * High level entry point called by controller when user applies voucher.
     * Returns summary array with subtotal, discount, grand_total and voucher data.
     *
     * @param string $code
     * @param \App\Models\User $user
     * @param array $context
     * @return array
     */
    public function applyToPaymentContext(string $code, $user, array $context): array
    {
        $voucher = Voucher::where('code', $code)->firstOrFail();
        $this->validateVoucher($voucher, $user, $context);
        $discount = $this->calculateDiscount($voucher, $context['subtotal']);
        $summary = $this->buildSummary($voucher, $context, $discount);
        return $summary;
    }

    public function validateVoucher(Voucher $voucher, $user, array $context): void
    {
        // active flag
        if (! $voucher->is_active) {
            throw new \Exception('Voucher tidak aktif');
        }
        $now = Carbon::now();
        if ($voucher->start_at && $now->lt($voucher->start_at)) {
            throw new \Exception('Voucher belum mulai berlaku');
        }
        if ($voucher->end_at && $now->gt($voucher->end_at)) {
            throw new \Exception('Voucher sudah kadaluarsa');
        }

        if ($voucher->min_purchase && $context['subtotal'] < $voucher->min_purchase) {
            throw new \Exception('Pembelian minimum belum tercapai');
        }

        $this->validateQuota($voucher, $user);
        $this->validateUserEligibility($voucher, $user);
        $this->validateItemEligibility($voucher, $context);
    }

    public function validateQuota(Voucher $voucher, $user): void
    {
        // global quota
        if ($voucher->quota !== null && $voucher->used_count >= $voucher->quota) {
            throw new \Exception('Kuota voucher sudah habis');
        }

        if ($voucher->per_user_limit !== null) {
            $count = VoucherUsage::where('voucher_id', $voucher->id)
                ->where('user_id', $user->id)
                ->count();
            if ($count >= $voucher->per_user_limit) {
                throw new \Exception('Anda sudah mencapai batas penggunaan voucher');
            }
        }

        // check pivot if user-specific
        if (! $voucher->applies_to_all_users) {
            $pivot = $voucher->users()->where('user_id', $user->id)->first();
            if ($pivot) {
                if ($pivot->pivot->max_usage !== null && $pivot->pivot->used_count >= $pivot->pivot->max_usage) {
                    throw new \Exception('Kuota penggunaan voucher untuk Anda sudah habis');
                }
            }
        }
    }

    public function validateUserEligibility(Voucher $voucher, $user): void
    {
        if ($voucher->applies_to_all_users) {
            return;
        }
        $allowed = $voucher->users()->where('user_id', $user->id)->exists();
        if (! $allowed) {
            throw new \Exception('Voucher tidak tersedia untuk Anda');
        }
    }

    public function validateItemEligibility(Voucher $voucher, array $context): void
    {
        if ($voucher->applies_to_all_items) {
            return;
        }
        // if voucher has no specific item rules, treat as applies to all
        $pivots = $voucher->voucherItems()->get();
        if ($pivots->isEmpty()) {
            return;
        }

        // collect all types+ids that this payment context matches
        $candidates = [
            ['type' => $context['itemType'], 'id' => $context['itemId']],
        ];

        // Helper: check if rule item_id matches candidate
        $itemMatches = function($ruleItemId, $candidateId) {
            // NULL means apply to all items of this type
            if ($ruleItemId === null) {
                return true;
            }
            
            // JSON array - check if candidate is in the array
            if (is_array($ruleItemId)) {
                return in_array($candidateId, $ruleItemId, true);
            }
            
            // Single item match
            return $ruleItemId == $candidateId;
        };

        // first check excludes – if any candidate is excluded, block
        // item_id can be NULL (all items), JSON array (multiple specific items), or single item
        foreach ($candidates as $c) {
            $excluded = $pivots->first(fn($pivot) =>
                $pivot->rule_type === 'exclude'
                && $pivot->item_type === $c['type']
                && $itemMatches(json_decode($pivot->item_id, true) ?? $pivot->item_id, $c['id'])
            );
            if ($excluded) {
                throw new \Exception('Voucher tidak berlaku untuk item ini');
            }
        }

        // if any include rules exist, ensure at least one candidate matches
        // item_id can be NULL (all items), JSON array (multiple specific items), or single item
        $includes = $pivots->filter(fn($pivot) => $pivot->rule_type === 'include');
        if ($includes->isNotEmpty()) {
            $matched = false;
            foreach ($candidates as $c) {
                if ($includes->first(fn($pivot) => 
                    $pivot->item_type === $c['type'] 
                    && $itemMatches(json_decode($pivot->item_id, true) ?? $pivot->item_id, $c['id'])
                )) {
                    $matched = true;
                    break;
                }
            }
            if (! $matched) {
                throw new \Exception('Voucher tidak berlaku untuk item ini');
            }
        }
        // if only excludes existed and we survived above, it's allowed
    }

    public function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        if ($voucher->discount_type === 'percent') {
            $amount = $subtotal * ($voucher->discount_value / 100);
            if ($voucher->max_discount !== null) {
                $amount = min($amount, $voucher->max_discount);
            }
        } else {
            $amount = $voucher->discount_value;
        }
        return min($amount, $subtotal);
    }

    public function buildSummary(Voucher $voucher, array $context, float $discount): array
    {
        $grand = max($context['subtotal'] - $discount, 0);
        return [
            'subtotal' => $context['subtotal'],
            'discount' => round($discount, 2),
            'grand_total' => round($grand, 2),
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'name' => $voucher->name,
                'discount_type' => $voucher->discount_type,
                'discount_value' => $voucher->discount_value,
            ],
        ];
    }

    /**
     * Record that a voucher was consumed. Must be called within transaction.
     *
     * @param Voucher $voucher
     * @param \App\Models\User $user
     * @param mixed $order  object or array containing order identifiers
     * @param array $summary
     * @return void
     */
    public function markAsUsed(Voucher $voucher, $user, $order, array $summary): void
    {
        // lock row for update to prevent race condition
        $voucher = Voucher::where('id', $voucher->id)->lockForUpdate()->first();

        $usageData = [
            'voucher_id' => $voucher->id,
            'user_id' => $user->id,
            'order_id' => $order->id ?? null,
            'order_type' => get_class($order) ?? null,
            'item_type' => $summary['context']['itemType'] ?? null,
            'item_id' => $summary['context']['itemId'] ?? null,
            'voucher_code' => $voucher->code,
            'discount_type' => $voucher->discount_type,
            'discount_value' => $voucher->discount_value,
            'discount_amount' => $summary['discount'],
            'subtotal' => $summary['subtotal'],
            'grand_total' => $summary['grand_total'],
            'payload' => $summary['context'],
            'used_at' => Carbon::now(),
        ];

        VoucherUsage::create($usageData);

        // increment counters
        $voucher->increment('used_count');

        if (! $voucher->applies_to_all_users) {
            $pivot = $voucher->users()->where('user_id', $user->id)->first();
            if ($pivot) {
                $pivot->pivot->increment('used_count');
            }
        }
    }
}
