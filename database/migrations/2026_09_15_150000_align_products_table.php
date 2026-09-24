<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'category')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('category')->default('Autre')->after('description');
                $table->string('origin_country')->default('Non spécifié')->after('category');
                $table->string('origin_region')->nullable()->after('origin_country');
                $table->foreignId('producer_id')->nullable()->after('origin_region')->constrained('users')->nullOnDelete();
                $table->string('unit', 50)->default('kg')->after('producer_id');
                $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending')->after('carbon_footprint');
            });
        }

        if (Schema::hasColumn('products', 'user_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropUnique(['slug']);
                $table->dropColumn(['slug', 'is_local', 'is_fair_trade', 'user_id']);
            });
        }

        if (Schema::hasColumn('products', 'producer_id')) {
            DB::table('products')->whereNull('producer_id')->update([
                'producer_id' => DB::table('users')->value('id'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique();
            $table->boolean('is_local')->default(false);
            $table->boolean('is_fair_trade')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['producer_id']);
            $table->dropColumn(['category', 'origin_country', 'origin_region', 'producer_id', 'unit', 'verification_status']);
        });
    }
};