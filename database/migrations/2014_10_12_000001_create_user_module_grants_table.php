<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_module_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module', 40);
            $table->string('level', 10);
            $table->timestamps();

            $table->unique(['user_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_module_grants');
    }
};
