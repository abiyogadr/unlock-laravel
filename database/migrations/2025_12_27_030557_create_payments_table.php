<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relasi ke registrations
            $table->foreignId('registration_id')
                  ->constrained('registrations')
                  ->cascadeOnDelete();

            $table->string('registration_code')->index();

            // Status (string, tanpa enum)
            $table->string('payment_status', 20)->index();
            $table->string('registration_status', 20)->index();

            // Midtrans info
            $table->string('payment_type', 50)->nullable();
            $table->string('transaction_id', 100)->nullable()->index();
            $table->decimal('gross_amount', 12, 2)->nullable();

            // Waktu
            $table->dateTime('transaction_time')->nullable();
            $table->dateTime('settlement_time')->nullable();
            $table->dateTime('paid_at')->nullable();

            // Raw callback
            $table->json('raw_callback')->nullable();

            $table->timestamps();

            // Cegah duplikat transaksi Midtrans
            $table->unique(['registration_id', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
