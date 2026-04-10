<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_links', function (Blueprint $table) {
            // Make label/url optional so text and image blocks don't need them
            $table->string('label')->nullable()->change();
            $table->string('url', 2048)->nullable()->change();

            // New columns for multi-type blocks
            $table->string('type')->default('link')->after('id');
            $table->text('content')->nullable()->after('url');
            $table->string('image_path')->nullable()->after('content');
            $table->json('elem_style')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('landing_page_links', function (Blueprint $table) {
            $table->dropColumn(['type', 'content', 'image_path', 'elem_style']);
            $table->string('label')->nullable(false)->change();
            $table->string('url', 2048)->nullable(false)->change();
        });
    }
};
