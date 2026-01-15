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
        Schema::create('speakers', function (Blueprint $table) {
            $table->id(); // PK

            $table->string('speaker_id')->unique(); // RS00038

            $table->string('speaker_name');

            $table->string('prefix_title')->nullable();
            $table->string('suffix_title')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('position')->nullable();
            $table->string('company')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speakers');
    }
};
