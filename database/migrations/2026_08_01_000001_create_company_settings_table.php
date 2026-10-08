<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name')->nullable();
            $table->string('nit', 40)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 120)->nullable();
            $table->string('phone', 60)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('legal_rep_name')->nullable();
            $table->string('legal_rep_document', 40)->nullable();
            $table->string('legal_rep_document_city', 120)->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_document', 40)->nullable();
            $table->string('agent_document_city', 120)->nullable();
            $table->string('internal_code_prefix', 10)->default('ARM-');
            $table->foreignId('logo_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('letterhead_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
