<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('weapon_import_batches')->cascadeOnDelete();
            $table->foreignId('weapon_id')->nullable()->constrained('weapons')->nullOnDelete();
            $table->foreignId('vest_id')->nullable()->constrained('vests')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('action');
            $table->string('execution_status')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('execution_error')->nullable();
            $table->string('summary')->nullable();
            $table->json('raw_payload')->nullable();
            $table->json('normalized_payload')->nullable();
            $table->json('before_payload')->nullable();
            $table->json('after_payload')->nullable();
            $table->json('errors')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'action']);
            $table->index(['batch_id', 'row_number']);
            $table->index(['batch_id', 'execution_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_import_rows');
    }
};
