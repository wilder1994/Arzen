<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vest_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('file_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->timestamps();

            $table->unique(['vest_id', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vest_photos');
    }
};
