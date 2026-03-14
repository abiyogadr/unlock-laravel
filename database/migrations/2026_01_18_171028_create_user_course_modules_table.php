<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_course_id')->constrained('user_courses')->onDelete('cascade');
            $table->foreignId('course_module_id')->constrained('course_modules')->onDelete('cascade');
            $table->decimal('progress', 5, 2)->default(0); // 0-100 with 2 decimal places
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique(['user_course_id', 'course_module_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_course_modules');
    }
};
