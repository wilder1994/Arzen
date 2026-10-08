<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_client_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->constrained('users');
            $table->date('start_at');
            $table->date('end_at')->nullable();
            $table->boolean('is_active')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->foreignId('support_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->timestamps();

            $table->unique(['weapon_id', 'is_active']);
            $table->index(['client_id', 'is_active'], 'weapon_client_assignments_client_active_idx');
            $table->index(['responsible_user_id', 'is_active'], 'weapon_client_assignments_responsible_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_client_assignments');
    }
};

