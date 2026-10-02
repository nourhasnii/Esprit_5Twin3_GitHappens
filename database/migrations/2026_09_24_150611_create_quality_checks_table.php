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
    Schema::create('quality_checks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
        $table->string('image_path')->nullable();
        $table->json('vision_result')->nullable();
        $table->decimal('quality_score', 5, 2)->nullable();
        $table->decimal('freshness_score', 5, 2)->nullable();
        $table->enum('decision', ['sell', 'donate', 'withdraw', 'inspect'])->nullable();
        $table->decimal('confidence', 5, 2)->nullable();
        $table->text('explanation')->nullable();
        $table->json('factors')->nullable();
        $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_checks');
    }
};
