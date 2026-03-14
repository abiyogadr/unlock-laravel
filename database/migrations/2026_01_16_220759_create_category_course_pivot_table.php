<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('category_course', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('course_categories')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->primary(['category_id', 'course_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_course');
    }
};
