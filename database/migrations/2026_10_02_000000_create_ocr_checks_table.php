<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocr_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image_path');
            $table->json('ocr_result')->nullable();
            $table->json('declared_snapshot')->nullable();
            $table->json('mismatches')->nullable();
            $table->decimal('inconsistency_score', 5, 2)->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('explanation')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocr_checks');
    }
};
