<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            
            // Data Statis di Sertifikat
            $table->string('event_title');
            $table->string('event_subtitle')->nullable();
            $table->string('speaker');
            $table->string('speaker_title')->nullable();
            $table->string('date_string');
            $table->string('date_extra')->nullable();
            
            // Pengaturan Visual
            $table->string('template')->default('1');
            $table->string('has_sign')->nullable();
            $table->string('sign_path');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_certificates');
    }

};
