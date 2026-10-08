<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weapon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->string('document_name')->nullable();
            $table->string('document_number')->nullable();
            $table->string('permit_kind')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('observations')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_permit')->default(false);
            $table->boolean('is_renewal')->default(false);
            $table->timestamps();

            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_documents');
    }
};

