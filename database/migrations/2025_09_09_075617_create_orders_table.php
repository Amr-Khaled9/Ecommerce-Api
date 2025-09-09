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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->string('shipping_name');
            $table->string('shipping_address');
            $table->string('shipping_status')->nullable();
            $table->string('shipping_zipcode');
            $table->string('shipping_country');
            $table->string('shipping_phone');
            $table->decimal('subtotal',8,2);
            $table->decimal('tex',8,2)->default(0);
            $table->decimal('shipping_cost',8,2)->default(0);
            $table->decimal('total',8,2);
            $table->string('payment_method')->default('cod');
            $table->string('payment_status')->default('pending');
            $table->string('order_number')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
