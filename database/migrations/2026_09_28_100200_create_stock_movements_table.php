<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convention : quantity est toujours positive, le sens est donné par les sites.
     *  - in         : destination seule
     *  - out        : source seule
     *  - transfer   : source + destination
     *  - adjustment : destination (écart positif) ou source (écart négatif)
     * Le stock d'un site = somme(destination) - somme(source).
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->string('type', 20);
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('destination_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('moved_at');
            $table->timestamps();

            $table->index(['type', 'moved_at']);
            $table->index(['product_id', 'source_site_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
