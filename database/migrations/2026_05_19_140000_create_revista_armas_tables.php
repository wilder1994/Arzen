<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temporary_photo_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_responsible_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('email', 190);
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();

            $table->index(['owner_responsible_user_id', 'is_active'], 'tpu_owner_active_idx');
            $table->index(['is_shared', 'is_active'], 'tpu_shared_active_idx');
            $table->index('email', 'tpu_email_idx');
        });

        Schema::create('temporary_photo_user_responsibles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temporary_photo_user_id');
            $table->unsignedBigInteger('responsible_user_id');
            $table->unsignedBigInteger('assigned_by_user_id');
            $table->timestamps();

            $table->foreign('temporary_photo_user_id', 'tpur_temp_user_fk')
                ->references('id')->on('temporary_photo_users')->cascadeOnDelete();
            $table->foreign('responsible_user_id', 'tpur_responsible_fk')
                ->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('assigned_by_user_id', 'tpur_assigned_by_fk')
                ->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['temporary_photo_user_id', 'responsible_user_id'], 'tpur_user_responsible_unique');
        });

        Schema::create('temporary_photo_access_grants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temporary_photo_user_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->foreign('temporary_photo_user_id', 'tpag_temp_user_fk')
                ->references('id')->on('temporary_photo_users')->restrictOnDelete();
            $table->foreign('created_by_user_id', 'tpag_created_by_fk')
                ->references('id')->on('users')->cascadeOnDelete();
            $table->string('access_code_hash');
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['temporary_photo_user_id', 'expires_at'], 'tpag_user_expires_idx');
            $table->index('revoked_at', 'tpag_revoked_idx');
        });

        Schema::create('temporary_photo_access_weapons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temporary_photo_access_grant_id');
            $table->unsignedBigInteger('weapon_id');
            $table->foreign('temporary_photo_access_grant_id', 'tpaw_grant_fk')
                ->references('id')->on('temporary_photo_access_grants')->cascadeOnDelete();
            $table->foreign('weapon_id', 'tpaw_weapon_fk')
                ->references('id')->on('weapons')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['temporary_photo_access_grant_id', 'weapon_id'], 'tpaw_grant_weapon_unique');
        });

        Schema::create('weapon_photo_staging', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('temporary_photo_user_id');
            $table->unsignedBigInteger('weapon_id');
            $table->string('description', 100);
            $table->unsignedBigInteger('file_id');
            $table->foreign('temporary_photo_user_id', 'wps_temp_user_fk')
                ->references('id')->on('temporary_photo_users')->restrictOnDelete();
            $table->foreign('weapon_id', 'wps_weapon_fk')
                ->references('id')->on('weapons')->cascadeOnDelete();
            $table->foreign('file_id', 'wps_file_fk')
                ->references('id')->on('files')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['temporary_photo_user_id', 'weapon_id', 'description'],
                'wps_user_weapon_desc_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_photo_staging');
        Schema::dropIfExists('temporary_photo_access_weapons');
        Schema::dropIfExists('temporary_photo_access_grants');
        Schema::dropIfExists('temporary_photo_user_responsibles');
        Schema::dropIfExists('temporary_photo_users');
    }
};
