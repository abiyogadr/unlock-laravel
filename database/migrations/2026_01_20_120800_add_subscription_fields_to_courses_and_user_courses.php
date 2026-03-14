<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'credit_cost')) {
                $table->unsignedInteger('credit_cost')->default(1)->after('price');
            }
            if (!Schema::hasColumn('courses', 'access_duration_days')) {
                $table->unsignedInteger('access_duration_days')->nullable()->after('credit_cost');
            }
        });

        Schema::table('user_courses', function (Blueprint $table) {
            if (!Schema::hasColumn('user_courses', 'subscription_id')) {
                $table->foreignId('subscription_id')->nullable()->after('course_id')->constrained('user_subscriptions')->nullOnDelete();
            }
            if (!Schema::hasColumn('user_courses', 'access_expires_at')) {
                $table->timestamp('access_expires_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('user_courses', 'acquisition_type')) {
                $table->enum('acquisition_type', ['idr', 'ustar', 'free', 'subscription'])->default('direct')->after('access_expires_at');
            }
            if (!Schema::hasColumn('user_courses', 'module_progress')) {
                $table->decimal('module_progress', 5, 2)->default(0)->after('progress');
            }

            $table->index(['subscription_id']);
            $table->index(['access_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('user_courses', function (Blueprint $table) {
            $table->dropIndex(['subscription_id']);
            $table->dropIndex(['access_expires_at']);
            if (Schema::hasColumn('user_courses', 'subscription_id')) {
                $table->dropColumn('subscription_id');
            }
            if (Schema::hasColumn('user_courses', 'access_expires_at')) {
                $table->dropColumn('access_expires_at');
            }
            if (Schema::hasColumn('user_courses', 'acquisition_type')) {
                $table->dropColumn('acquisition_type');
            }
            if (Schema::hasColumn('user_courses', 'module_progress')) {
                $table->dropColumn('module_progress');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'credit_cost')) {
                $table->dropColumn('credit_cost');
            }
            if (Schema::hasColumn('courses', 'access_duration_days')) {
                $table->dropColumn('access_duration_days');
            }
        });
    }
};
