<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() 
    {
        Schema::table('events', function (Blueprint $table) {
            // Menambahkan kolom is_attendance_open setelah kolom status
            $table->boolean('is_attendance_open')->default(false)->after('status');
        });
    }

    public function down() 
    {
        Schema::table('events', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn('is_attendance_open');
        });
    }

};
