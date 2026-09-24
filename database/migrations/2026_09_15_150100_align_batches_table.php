<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('batches', 'code')) {
            return;
        }

        Schema::table('batches', function (Blueprint $table) {
            $table->renameColumn('code', 'lot_number');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->renameColumn('expiry_date', 'expiration_date');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->decimal('carbon_footprint', 10, 2)->nullable()->after('unit');
        });

        DB::table('batches')->where('status', 'created')->update(['status' => 'active']);
        DB::table('batches')->whereIn('status', ['in_transit', 'delivered', 'sold'])->update(['status' => 'active']);

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('qr_code');
            $table->dropColumn('status');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->enum('status', ['active', 'expired', 'recalled'])->default('active')->after('carbon_footprint');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->string('qr_code')->nullable()->after('lot_number');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
            $table->enum('status', ['created', 'in_transit', 'delivered', 'sold', 'recalled'])->default('created')->after('unit');
            $table->dropColumn('carbon_footprint');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->renameColumn('lot_number', 'code');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->renameColumn('expiration_date', 'expiry_date');
        });
    }
};