<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->enum('package_type', ['ustar', 'subscription', 'monthly'])->default('subscription');
            $table->enum('plan_duration', ['1_month', '3_months', '6_months'])->nullable();
            $table->unsignedInteger('certificate_quota')->default(0); 
            $table->unsignedInteger('ustar_credits')->default(0);
            $table->unsignedInteger('duration_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_packages');
    }
};
