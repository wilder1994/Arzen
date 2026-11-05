<?php

use App\Models\ContractType;
use App\Models\EngagementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        foreach ([
            'Dependiente',
            'Independiente',
            'Aprendiz',
            'En misión',
        ] as $index => $name) {
            DB::table('engagement_types')->insert([
                'name' => $name,
                'is_active' => true,
                'sort_order' => ($index + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            'Término fijo',
            'Término indefinido',
            'Obra o labor',
            'Prestación de servicios',
            'Aprendizaje',
        ] as $index => $name) {
            DB::table('contract_types')->insert([
                'name' => $name,
                'is_active' => true,
                'sort_order' => ($index + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('workers', function (Blueprint $table) {
            $table->foreignId('engagement_type_id')->nullable()->after('same_cost_center')->constrained('engagement_types')->nullOnDelete();
            $table->foreignId('contract_type_id')->nullable()->after('contributor_type')->constrained('contract_types')->nullOnDelete();
        });

        foreach (DB::table('workers')->select('id', 'engagement_type', 'labor_contract_type')->get() as $worker) {
            DB::table('workers')->where('id', $worker->id)->update([
                'engagement_type_id' => EngagementType::resolveByName($worker->engagement_type)?->id,
                'contract_type_id' => ContractType::resolveByName($worker->labor_contract_type)?->id,
            ]);
        }

        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['engagement_type', 'labor_contract_type']);
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('workers', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        DB::statement('ALTER TABLE workers MODIFY client_id BIGINT UNSIGNED NULL');

        Schema::table('workers', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('workers', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
            });

            DB::statement('ALTER TABLE workers MODIFY client_id BIGINT UNSIGNED NOT NULL');

            Schema::table('workers', function (Blueprint $table) {
                $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            });
        }

        Schema::table('workers', function (Blueprint $table) {
            $table->string('engagement_type')->nullable();
            $table->string('labor_contract_type')->nullable();
        });

        Schema::table('workers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('engagement_type_id');
            $table->dropConstrainedForeignId('contract_type_id');
        });

        Schema::dropIfExists('contract_types');
        Schema::dropIfExists('engagement_types');
    }
};
