<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('voucher_code', 100)->nullable()->after('price');
            $table->decimal('voucher_discount', 12, 2)->default(0)->after('voucher_code');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['voucher_code', 'voucher_discount']);
        });
    }
};
