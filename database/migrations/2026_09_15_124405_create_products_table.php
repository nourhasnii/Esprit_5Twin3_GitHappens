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
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('origin_country');
            $table->string('origin_region')->nullable();
            $table->foreignId('producer_id')->constrained('users')->cascadeOnDelete();
            $table->string('unit', 50);
            $table->string('image')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->boolean('is_organic')->default(false);
            $table->decimal('carbon_footprint', 8, 2)->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
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
