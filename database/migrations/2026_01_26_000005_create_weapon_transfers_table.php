<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users');
            $table->foreignId('to_user_id')->constrained('users');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('from_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('new_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('status');
            $table->dateTime('requested_at');
            $table->dateTime('answered_at')->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('ammo_count')->nullable();
            $table->unsignedInteger('provider_count')->nullable();
            $table->timestamps();

            $table->index(['to_user_id', 'status']);
            $table->index(['weapon_id', 'status']);
            $table->index(['from_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_transfers');
    }
};

