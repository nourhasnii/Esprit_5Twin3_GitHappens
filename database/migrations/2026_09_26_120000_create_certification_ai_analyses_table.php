<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certification_ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->text('summary');
            $table->text('risk_explanation');
            $table->json('key_insights');
            $table->json('priority_actions');
            $table->text('business_impact');
            $table->float('confidence', 5, 2)->nullable();
            $table->unsignedTinyInteger('compliance_score')->nullable();
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->string('risk_level', 16)->nullable();
            $table->string('compliance_level', 16)->nullable();
            $table->string('model', 64)->nullable();
            $table->string('prompt_version', 16)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certification_ai_analyses');
    }
};
