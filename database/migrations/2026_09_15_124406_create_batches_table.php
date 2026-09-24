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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('lot_number')->unique();
            $table->date('production_date');
            $table->date('expiration_date');
            $table->decimal('quantity', 12, 2);
            $table->string('unit', 50);
            $table->decimal('carbon_footprint', 10, 2)->nullable();
            $table->enum('status', ['active', 'expired', 'recalled'])->default('active')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
