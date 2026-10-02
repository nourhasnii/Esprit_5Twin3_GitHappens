<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optimization_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('recommended_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('chosen_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->decimal('quantity', 12, 2);

            // Résultat IA stocké pour réutilisation (Decision Engine)
            $table->decimal('score', 5, 1)->default(0);
            $table->decimal('distance_km', 8, 1)->nullable();
            $table->decimal('co2_kg', 10, 2)->nullable();
            $table->integer('days_to_expiry')->nullable();
            $table->json('weights');
            $table->json('candidates');
            $table->json('explanation');

            $table->string('status', 20)->default('pending');
            $table->text('decision_note')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('stock_movement_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optimization_recommendations');
    }
};
