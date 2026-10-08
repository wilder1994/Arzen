<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->string('brand')->nullable();
            $table->string('batch')->nullable();
            $table->string('size')->nullable();
            $table->date('manufactured_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('device_responsible')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'expires_at']);
            $table->index('worker_id');
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vests');
    }
};
