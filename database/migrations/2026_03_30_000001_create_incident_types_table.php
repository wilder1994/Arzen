<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('color', 20)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('requires_modality')->default(false);
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('requires_resolution_note')->default(false);
            $table->string('default_status', 30)->default('open');
            $table->unsignedSmallInteger('sla_hours')->nullable();
            $table->boolean('blocks_operation')->default(false);
            $table->boolean('persists_operational_block')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_reportable')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_types');
    }
};
