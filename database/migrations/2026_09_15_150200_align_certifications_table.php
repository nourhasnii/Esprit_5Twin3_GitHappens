<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('certifications', 'product_id') && Schema::hasColumn('certifications', 'certificate_number') && ! Schema::hasColumn('certifications', 'code')) {
            return;
        }

        if (! Schema::hasColumn('certifications', 'product_id')) {
            Schema::table('certifications', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->string('certificate_number')->nullable()->after('name');
                $table->string('issuing_organization')->nullable()->after('certificate_number');
                $table->date('issued_at')->nullable()->after('issuing_organization');
                $table->date('expires_at')->nullable()->after('issued_at');
                $table->string('document_path')->nullable()->after('expires_at');
                $table->enum('status', ['valid', 'expiring', 'expired', 'pending'])->default('pending')->after('document_path');
                $table->text('notes')->nullable()->after('status');
            });
        }

        if (Schema::hasColumn('certifications', 'code')) {
            DB::table('certifications')->whereNull('certificate_number')->update([
                'certificate_number' => DB::raw("CONCAT('LEGACY-', id)"),
            ]);
        }

        Schema::table('certifications', function (Blueprint $table) {
            if (Schema::hasColumn('certifications', 'code')) {
                $table->dropColumn(['code', 'description', 'logo']);
            }
        });

        if (Schema::hasColumn('certifications', 'certificate_number')) {
            Schema::table('certifications', function (Blueprint $table) {
                $table->unique('certificate_number');
            });
        }
    }

    public function down(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->dropUnique(['certificate_number']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'certificate_number', 'issuing_organization', 'issued_at', 'expires_at', 'document_path', 'status', 'notes']);
        });
    }
};