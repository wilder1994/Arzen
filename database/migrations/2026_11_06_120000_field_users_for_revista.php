<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('weapon_photo_staging');
        Schema::dropIfExists('temporary_photo_access_weapons');
        Schema::dropIfExists('temporary_photo_access_grants');
        Schema::dropIfExists('temporary_photo_user_responsibles');
        Schema::dropIfExists('temporary_photo_users');

        Schema::table('users', function (Blueprint $table) {
            $table->string('account_kind', 20)->default('platform')->after('role');
            $table->string('zone', 120)->nullable()->after('account_kind');
            $table->foreignId('field_owner_id')->nullable()->after('zone')->constrained('users')->nullOnDelete();
            $table->boolean('field_shared')->default(false)->after('field_owner_id');
            $table->unsignedTinyInteger('field_day_start')->default(1)->after('field_shared');
            $table->unsignedTinyInteger('field_day_end')->default(5)->after('field_day_start');
        });

        Schema::create('field_user_responsibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['field_user_id', 'responsible_user_id'], 'field_user_responsible_unique');
        });

        Schema::create('field_user_weapons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('weapon_id')->constrained('weapons')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['field_user_id', 'weapon_id'], 'field_user_weapon_unique');
        });

        Schema::create('weapon_photo_staging', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('weapon_id')->constrained('weapons')->cascadeOnDelete();
            $table->string('description', 100);
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'weapon_id', 'description'], 'wps_user_weapon_desc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapon_photo_staging');
        Schema::dropIfExists('field_user_weapons');
        Schema::dropIfExists('field_user_responsibles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('field_owner_id');
            $table->dropColumn(['account_kind', 'zone', 'field_shared', 'field_day_start', 'field_day_end']);
        });
    }
};
