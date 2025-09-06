<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // macBook12 -> name product
            $table->string('slug')->unique(); // mac-book-12 in url
            $table->text('description')->nullable();
            $table->decimal('price',8,2);
            $table->integer('stock')->default(0);
            $table->string('sku')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
