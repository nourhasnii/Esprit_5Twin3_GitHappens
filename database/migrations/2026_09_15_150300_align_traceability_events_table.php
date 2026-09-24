<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('traceability_events', 'actor_id')) {
            return;
        }

        Schema::table('traceability_events', function (Blueprint $table) {
            $table->string('event_type')->index()->change();
            $table->string('location')->nullable()->change();
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->foreignId('actor_id')->nullable()->after('longitude')->constrained('users')->nullOnDelete();
            $table->text('description')->nullable()->after('actor_id');
            $table->decimal('quantity', 12, 2)->nullable()->after('description');
            $table->decimal('distance_km', 12, 2)->nullable()->after('temperature');
            $table->decimal('carbon_emission', 12, 2)->nullable()->after('distance_km');
            $table->json('metadata')->nullable()->after('carbon_emission');
        });

        DB::table('traceability_events')->whereNotNull('user_id')->update([
            'actor_id' => DB::raw('user_id'),
        ]);

        Schema::table('traceability_events', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'humidity', 'notes']);
        });
    }

    public function down(): void
    {
        Schema::table('traceability_events', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->dropForeign(['actor_id']);
            $table->dropColumn(['latitude', 'longitude', 'actor_id', 'description', 'quantity', 'distance_km', 'carbon_emission', 'metadata']);
        });
    }
};