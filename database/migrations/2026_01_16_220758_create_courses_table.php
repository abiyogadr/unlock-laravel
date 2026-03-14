<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->enum('level', ['pemula', 'menengah', 'lanjut'])->default('pemula');
            $table->boolean('is_free')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('speaker_id')->nullable()->constrained('speakers')->nullOnDelete();
            $table->string('kv_path')->nullable(); // Key Visual
            $table->timestamps();
            
            $table->index(['status', 'is_free']);
            $table->index('slug');
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
