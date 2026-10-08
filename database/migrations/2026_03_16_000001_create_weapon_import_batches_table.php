<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->string('type')->default('weapon');
            $table->string('source_name');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('create_count')->default(0);
            $table->unsignedInteger('update_count')->default(0);
            $table->unsignedInteger('no_change_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['type', 'status'], 'weapon_import_batches_type_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_import_batches');
    }
};
