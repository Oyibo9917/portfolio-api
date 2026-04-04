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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Auto-incrementing id
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('image');
            $table->decimal('price', 10, 2); // Price with 2 decimal places
            $table->string('category');
            $table->integer('quantity');
            $table->enum('inventory_status', ['INSTOCK', 'OUTOFSTOCK', 'PENDING']);
            $table->tinyInteger('rating')->nullable(); // Rating from 1-5
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
