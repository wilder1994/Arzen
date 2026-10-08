<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_incident_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_incident_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->text('note')->nullable();
            $table->foreignId('attachment_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->timestamp('happened_at')->nullable();
            $table->string('status_from', 30)->nullable();
            $table->string('status_to', 30)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['weapon_incident_id', 'happened_at']);
            $table->index(['weapon_incident_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_incident_updates');
    }
};