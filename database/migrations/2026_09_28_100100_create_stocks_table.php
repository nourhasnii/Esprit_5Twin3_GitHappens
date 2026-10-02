<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // null = stock suivi au niveau produit, sans lot
            $table->foreignId('batch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(0);
            // Seuil de rupture du produit sur ce site (identique sur toutes ses lignes)
            $table->decimal('min_threshold', 12, 2)->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'product_id', 'batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
