<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->json('style')->nullable()->after('template_type');
        });

        Schema::table('landing_page_links', function (Blueprint $table) {
            $table->string('thumbnail')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn('style');
        });

        Schema::table('landing_page_links', function (Blueprint $table) {
            $table->dropColumn('thumbnail');
        });
    }
};
