<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->enum('module_type', ['video', 'pdf'])->default('video')->after('video_path');
            $table->string('pdf_path')->nullable()->after('module_type');
        });
    }

    public function down()
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->dropColumn(['module_type', 'pdf_path']);
        });
    }
};
