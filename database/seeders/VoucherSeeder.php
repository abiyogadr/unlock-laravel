<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voucher;
use App\Models\VoucherUser;
use App\Models\VoucherItem;
use App\Models\User;
use App\Models\Ecourse\Course;
use App\Models\Ecourse\CoursePackage;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run()
    {
        // 1. Simple percent voucher applicable to all users and items
        Voucher::create([
            'code' => 'HEMAT10',
            'name' => 'Hemat 10%',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'is_active' => true,
            'applies_to_all_users' => true,
            'applies_to_all_items' => true,
            'start_at' => now()->subDay(),
            'end_at' => now()->addMonth(),
        ]);

        // 2. Percent with max discount, for all users/items
        Voucher::create([
            'code' => 'PINTAR50',
            'name' => 'Diskon 50% max 25000',
            'discount_type' => 'percent',
            'discount_value' => 50,
            'max_discount' => 25000,
            'is_active' => true,
            'applies_to_all_users' => true,
            'applies_to_all_items' => true,
        ]);

        // 3. Voucher limited to a specific package
        $voucher = Voucher::create([
            'code' => 'PAKETKHUSUS',
            'name' => 'Potongan Paket tertentu',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'is_active' => true,
            'applies_to_all_users' => true,
            'applies_to_all_items' => false,
        ]);
        $package = CoursePackage::first();
        if ($package) {
            VoucherItem::create([
                'voucher_id' => $voucher->id,
                'item_type' => CoursePackage::class,
                'item_id' => $package->id,
                'rule_type' => 'include',
            ]);
        }

        // 4. Voucher limited to a specific course
        $courseVoucher = Voucher::create([
            'code' => 'KURSUSHEMAT',
            'name' => 'Diskon 30% untuk kursus tertentu',
            'discount_type' => 'percent',
            'discount_value' => 30,
            'max_discount' => 50000,
            'is_active' => true,
            'applies_to_all_users' => true,
            'applies_to_all_items' => false,
        ]);
        $course = Course::first();
        if ($course) {
            VoucherItem::create([
                'voucher_id' => $courseVoucher->id,
                'item_type' => Course::class,
                'item_id' => $course->id,
                'rule_type' => 'include',
            ]);
        }

        // 5. Voucher only for a specific user
        $userOnly = Voucher::create([
            'code' => 'KHUSUSUSER',
            'name' => 'Voucher untuk user tertentu',
            'discount_type' => 'fixed',
            'discount_value' => 20000,
            'applies_to_all_users' => false,
            'applies_to_all_items' => true,
            'is_active' => true,
        ]);
        $user = User::first();
        if ($user) {
            VoucherUser::create([
                'voucher_id' => $userOnly->id,
                'user_id' => $user->id,
                'assigned_at' => Carbon::now(),
                'max_usage' => 1,
            ]);
        }

        // 6. Voucher for specific user + specific package
        $comboVoucher = Voucher::create([
            'code' => 'COMBO100',
            'name' => 'Voucher combo user + item',
            'discount_type' => 'fixed',
            'discount_value' => 100000,
            'applies_to_all_users' => false,
            'applies_to_all_items' => false,
            'is_active' => true,
            'quota' => 5,
            'per_user_limit' => 1,
        ]);
        if ($user && $package) {
            VoucherUser::create([
                'voucher_id' => $comboVoucher->id,
                'user_id' => $user->id,
                'assigned_at' => Carbon::now(),
                'max_usage' => 1,
            ]);
            VoucherItem::create([
                'voucher_id' => $comboVoucher->id,
                'item_type' => CoursePackage::class,
                'item_id' => $package->id,
                'rule_type' => 'include',
            ]);
        }
    }
}
