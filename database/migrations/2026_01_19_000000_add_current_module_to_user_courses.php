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
        Schema::table('user_courses', function (Blueprint $table) {
            // Tambah kolom untuk track modul yang sedang dikerjakan
            $table->foreignId('current_module_id')->nullable()->constrained('course_modules')->onDelete('set null')->after('course_id');
            
            // Ubah nama kolom progress menjadi module_progress untuk clarity
            // (modul yang sudah selesai dibanding total modul)
            // Atau bisa ditambah kolom untuk tracking waktu watched di modul
            $table->decimal('module_progress', 5, 2)->nullable()->default(0)->comment('Progress dalam modul saat ini (0-100)')->after('progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_courses', function (Blueprint $table) {
            $table->dropForeign(['current_module_id']);
            $table->dropColumn(['current_module_id', 'module_progress']);
        });
    }
};
