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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id(); // PK

            $table->foreignId('event_id');
            $table->foreignId('packet_id');

            // snapshot
            $table->string('event_code');
            $table->string('event_name');
            $table->string('packet_name');

            $table->string('full_name');
            $table->string('email');
            $table->string('whatsapp');

            $table->enum('gender', ['male', 'female'])->nullable();
            $table->unsignedTinyInteger('age')->nullable();

            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('profession')->nullable();

            $table->string('channel_information')->nullable();

            // Payment
            $table->integer('price')->default(0);
            $table->string('payment')->nullable();
            $table->string('status_payment')->default('pending');

            $table->string('screenshot_payment')->nullable();
            $table->string('screenshot_follow_ig')->nullable();
            $table->string('screenshot_follow_tiktok')->nullable();

            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('packet_id')->references('id')->on('packets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
