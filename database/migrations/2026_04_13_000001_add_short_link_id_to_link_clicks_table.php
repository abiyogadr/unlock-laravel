<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('link_clicks', function (Blueprint $table) {
            $table->foreignId('short_link_id')
                ->nullable()
                ->after('landing_page_link_id')
                ->constrained('short_links')
                ->cascadeOnDelete();

            $table->index(['short_link_id', 'clicked_at']);
        });

        DB::statement('ALTER TABLE link_clicks MODIFY landing_page_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE link_clicks MODIFY landing_page_link_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE link_clicks MODIFY landing_page_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE link_clicks MODIFY landing_page_link_id BIGINT UNSIGNED NOT NULL');

        Schema::table('link_clicks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('short_link_id');
        });
    }
};