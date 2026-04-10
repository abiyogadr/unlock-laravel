<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_links', function (Blueprint $table) {
            $table->string('label_2')->nullable()->after('label');
            $table->string('url_2', 2048)->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('landing_page_links', function (Blueprint $table) {
            $table->dropColumn(['label_2', 'url_2']);
        });
    }
};