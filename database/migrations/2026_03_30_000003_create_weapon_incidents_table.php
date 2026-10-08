<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('incident_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('incident_modality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('open');
            $table->string('observation', 255)->nullable();
            $table->text('note')->nullable();
            $table->dateTime('event_at');
            $table->dateTime('reported_at');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('source_document_id')->nullable()->constrained('weapon_documents')->nullOnDelete();
            $table->foreignId('attachment_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->dateTime('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->string('closure_outcome', 100)->nullable();
            $table->timestamps();

            $table->index(['incident_type_id', 'status', 'event_at'], 'weapon_incidents_type_status_event_idx');
            $table->index(['weapon_id', 'status', 'event_at'], 'weapon_incidents_weapon_status_event_idx');
            $table->index(['reported_at'], 'weapon_incidents_reported_at_idx');
            $table->index(['event_at'], 'weapon_incidents_event_at_idx');
            $table->index(['status', 'closure_outcome'], 'weapon_incidents_status_closure_outcome_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_incidents');
    }
};
