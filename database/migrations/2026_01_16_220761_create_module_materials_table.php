<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('module_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_module_id')->constrained('course_modules')->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['pdf', 'doc', 'ppt', 'zip']);
            $table->string('file_path');
            $table->string('file_size', 20)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('course_module_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_materials');
    }
};
