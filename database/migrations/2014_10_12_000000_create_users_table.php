<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('document_type', 20)->nullable();
            $table->string('document', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->enum('role', ['ADMIN', 'RESPONSABLE', 'AUDITOR', 'ALMACEN'])->default('ADMIN');
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

