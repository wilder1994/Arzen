<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('code', 40)->nullable();
            $table->boolean('is_client_site')->default(false);
            $table->string('address')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city', 120)->nullable();
            $table->string('department', 120)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'is_client_site']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};
