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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('cert_id')->unique();
            $table->string('name');
            $table->string('event_id');
            $table->string('event_title');
            $table->string('event_subtitle')->nullable();
            $table->string('speaker');
            $table->string('speaker_title');
            $table->string('date');
            $table->string('date_extra');
            $table->timestamps();
            
            // Index untuk performa query
            $table->index('cert_id');
            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
