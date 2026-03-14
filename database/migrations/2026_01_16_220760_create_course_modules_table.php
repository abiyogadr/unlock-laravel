<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->integer('order_num');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_path')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->json('objectives')->nullable();
            $table->timestamps();
            
            $table->unique(['course_id', 'slug']);
            $table->index(['course_id', 'order_num']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_modules');
    }
};
