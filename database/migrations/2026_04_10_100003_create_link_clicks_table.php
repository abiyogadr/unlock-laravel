<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('landing_page_link_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer', 2048)->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();

            $table->index(['landing_page_id', 'clicked_at']);
            $table->index(['landing_page_link_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_clicks');
    }
};
