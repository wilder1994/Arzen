<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('users', 'document_type')) {
                $table->string('document_type', 20)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'document')) {
                $table->string('document', 50)->nullable()->after('document_type');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('document');
            }
            if (! Schema::hasColumn('users', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('phone');
            }
            if (Schema::hasColumn('users', 'cost_center')) {
                $table->dropColumn('cost_center');
            }
        });

        DB::table('users')->select('id', 'name')->orderBy('id')->each(function (object $user): void {
            $parts = preg_split('/\s+/u', trim((string) $user->name), 2) ?: [];

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parts[0] ?? '',
                'last_name' => $parts[1] ?? '',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'cost_center')) {
                $table->string('cost_center', 100)->nullable();
            }

            $table->dropColumn(array_values(array_filter([
                Schema::hasColumn('users', 'first_name') ? 'first_name' : null,
                Schema::hasColumn('users', 'last_name') ? 'last_name' : null,
                Schema::hasColumn('users', 'document_type') ? 'document_type' : null,
                Schema::hasColumn('users', 'document') ? 'document' : null,
                Schema::hasColumn('users', 'phone') ? 'phone' : null,
                Schema::hasColumn('users', 'photo_path') ? 'photo_path' : null,
            ])));
        });
    }
};
