<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('order_type')->nullable();
            $table->string('item_type')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('voucher_code');
            $table->enum('discount_type', ['percent', 'fixed']);
            $table->decimal('discount_value', 15, 2);
            $table->decimal('discount_amount', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('grand_total', 15, 2);
            $table->json('payload')->nullable();
            $table->timestamp('used_at');
            $table->timestamps();

            $table->index(['voucher_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_usages');
    }
};
