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
        Schema::create('event_packets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id');
            $table->foreignId('packet_id');

            $table->timestamps();

            $table->unique(['event_id', 'packet_id']);

            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('packet_id')->references('id')->on('packets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_packets');
    }
};
