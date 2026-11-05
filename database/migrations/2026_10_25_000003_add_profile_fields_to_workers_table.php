<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->string('first_names')->nullable()->after('name');
            $table->string('last_name_paternal')->nullable()->after('first_names');
            $table->string('last_name_maternal')->nullable()->after('last_name_paternal');
            $table->string('sex', 40)->nullable()->after('document');
            $table->date('birth_date')->nullable()->after('sex');
            $table->unsignedSmallInteger('age')->nullable()->after('birth_date');
            $table->string('birth_department')->nullable()->after('age');
            $table->string('birth_city')->nullable()->after('birth_department');
            $table->string('nationality')->nullable()->after('birth_city');
            $table->string('blood_group', 20)->nullable()->after('nationality');
            $table->string('document_issue_department')->nullable()->after('blood_group');
            $table->string('document_issue_city')->nullable()->after('document_issue_department');
            $table->date('document_issued_at')->nullable()->after('document_issue_city');
            $table->string('has_disability', 80)->nullable()->after('document_issued_at');
            $table->string('phone', 40)->nullable()->after('has_disability');
            $table->string('email')->nullable()->after('phone');
            $table->string('address')->nullable()->after('email');
            $table->string('residence_city')->nullable()->after('address');
            $table->string('emergency_phone', 40)->nullable()->after('residence_city');
            $table->string('emergency_contact')->nullable()->after('emergency_phone');
            $table->string('collaborator_type')->nullable()->after('emergency_contact');
            $table->string('sector')->nullable()->after('collaborator_type');
            $table->string('same_cost_center', 40)->nullable()->after('sector');
            $table->string('engagement_type')->nullable()->after('same_cost_center');
            $table->string('contributor_type')->nullable()->after('engagement_type');
            $table->string('labor_contract_type')->nullable()->after('contributor_type');
            $table->date('hired_on')->nullable()->after('labor_contract_type');
            $table->date('labor_contract_ends_on')->nullable()->after('hired_on');
            $table->date('left_on')->nullable()->after('labor_contract_ends_on');
            $table->string('education')->nullable()->after('left_on');
            $table->string('marital_status', 80)->nullable()->after('education');
            $table->unsignedSmallInteger('children_count')->nullable()->after('marital_status');
            $table->string('eps_code', 40)->nullable()->after('children_count');
            $table->string('eps_name')->nullable()->after('eps_code');
            $table->string('afp_code', 40)->nullable()->after('eps_name');
            $table->string('afp_name')->nullable()->after('afp_code');
            $table->string('compensation_fund')->nullable()->after('afp_name');
            $table->string('arl_name')->nullable()->after('compensation_fund');
            $table->string('arl_risk_level', 40)->nullable()->after('arl_name');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn([
                'first_names', 'last_name_paternal', 'last_name_maternal', 'sex', 'birth_date', 'age',
                'birth_department', 'birth_city', 'nationality', 'blood_group',
                'document_issue_department', 'document_issue_city', 'document_issued_at', 'has_disability',
                'phone', 'email', 'address', 'residence_city', 'emergency_phone', 'emergency_contact',
                'collaborator_type', 'sector', 'same_cost_center',
                'engagement_type', 'contributor_type', 'labor_contract_type',
                'hired_on', 'labor_contract_ends_on', 'left_on',
                'education', 'marital_status', 'children_count',
                'eps_code', 'eps_name', 'afp_code', 'afp_name', 'compensation_fund', 'arl_name', 'arl_risk_level',
            ]);
        });
    }
};
