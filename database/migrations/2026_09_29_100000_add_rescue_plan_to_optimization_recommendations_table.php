<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plan anti-gaspillage : quand un lot ne peut pas être vendu à temps par un simple transfert,
 * le plan appliqué (transferts partiels, promotion, dons) est conservé avec la recommandation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('optimization_recommendations', function (Blueprint $table) {
            $table->json('rescue_plan')->nullable()->after('explanation');
            $table->timestamp('rescue_applied_at')->nullable()->after('rescue_plan');
            $table->foreignId('rescue_applied_by')->nullable()->after('rescue_applied_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('optimization_recommendations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rescue_applied_by');
            $table->dropColumn(['rescue_plan', 'rescue_applied_at']);
        });
    }
};
