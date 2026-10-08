<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_authenticated_templates', function (Blueprint $table) {
            $table->id();
            $table->string('permit_kind', 20)->unique();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_authenticated_templates');
    }
};
