<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status')->default('active')->index();
            $table->string('organization_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->text('address')->nullable();
            $table->string('professional_identifier')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('information_requested_at')->nullable();
            $table->foreignId('information_requested_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['information_requested_by']);
            $table->dropColumn(['account_status', 'organization_name', 'phone', 'country', 'region', 'address', 'professional_identifier', 'must_change_password', 'approved_at', 'approved_by', 'rejected_at', 'rejected_by', 'rejection_reason', 'information_requested_at', 'information_requested_by']);
        });
    }
};