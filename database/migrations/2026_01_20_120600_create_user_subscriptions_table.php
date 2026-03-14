<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained('course_packages')->onDelete('cascade');
            $table->enum('subscription_type', ['ustar', 'monthly'])->nullable();
            $table->enum('plan_duration', ['1_month', '3_months', '6_months'])->nullable();
            $table->timestamp('start_date')->useCurrent();
            $table->timestamp('end_date')->nullable();
            $table->unsignedInteger('certificate_quota')->default(0); 
            $table->unsignedInteger('certificate_used')->default(0);
            $table->unsignedInteger('ustar_total')->default(0);
            $table->unsignedInteger('ustar_used')->default(0);
            $table->enum('status', ['active', 'expired', 'canceled', 'pending'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
