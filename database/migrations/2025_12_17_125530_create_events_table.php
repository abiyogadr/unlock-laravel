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
        Schema::create('events', function (Blueprint $table) {
            $table->id(); // PK

            $table->string('event_code')->unique();

            $table->string('event_title');

            $table->date('date_start');
            $table->date('date_end');

            $table->string('year', 4);
            $table->string('month', 2);

            $table->string('time_start');
            $table->string('time_end');

            $table->string('speaker1_id');
            $table->string('speaker1_name');

            $table->string('speaker2_id')->nullable();
            $table->string('speaker2_name')->nullable();

            $table->string('speaker3_id')->nullable();
            $table->string('speaker3_name')->nullable();

            $table->string('classification')->nullable();
            $table->string('collaboration')->nullable();
            $table->string('status')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
