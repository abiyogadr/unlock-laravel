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
        Schema::create('upload_proofs', function (Blueprint $table) {
            $table->id();
            $table->morphs('uploadable'); // Creates uploadable_id & uploadable_type
            $table->string('category'); // follow_ig, repost_story, etc.
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['uploadable_type', 'uploadable_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_proofs');
    }
};
