<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapon_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('weapon_calibers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('company_settings') && Schema::hasColumn('weapons', 'ownership_entity')) {
            $legalName = trim((string) DB::table('company_settings')->value('legal_name'));
            if ($legalName !== '') {
                DB::table('weapons')
                    ->where(function ($query) {
                        $query->whereNull('ownership_entity')->orWhere('ownership_entity', '');
                    })
                    ->update(['ownership_entity' => $legalName]);
            }
        }

        if (Schema::hasColumn('weapons', 'ownership_type')) {
            Schema::table('weapons', function (Blueprint $table) {
                $table->dropColumn('ownership_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('weapons', function (Blueprint $table) {
            $table->string('ownership_type')->default('company_owned')->after('capacity');
        });

        Schema::dropIfExists('weapon_calibers');
        Schema::dropIfExists('weapon_brands');
    }
};
