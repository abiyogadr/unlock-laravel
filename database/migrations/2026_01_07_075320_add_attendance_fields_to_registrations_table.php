<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->boolean('is_attended')->default(false)->after('registration_status');
            $table->timestamp('attended_at')->nullable()->after('is_attended');
            
            $table->string('attendance_proof')->nullable()->after('attended_at');
            $table->string('rating', 20)->nullable()->after('flag_sub');
            $table->json('feedback')->nullable()->after('rating');
            $table->text('next_theme_suggestion')->nullable()->after('feedback');
        });
    }

    public function down()
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'is_attended', 'attended_at', 
                'attendance_proof', 'rating', 'feedback', 'next_theme_suggestion'
            ]);
        });
    }

};
