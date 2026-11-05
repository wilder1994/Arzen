<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'post_id',
        'name',
        'document_type',
        'document',
        'position_id',
        'responsible_user_id',
        'notes',
        'archived_at',
        'first_names',
        'last_name_paternal',
        'last_name_maternal',
        'sex',
        'birth_date',
        'age',
        'birth_department',
        'birth_city',
        'nationality',
        'blood_group',
        'document_issue_department',
        'document_issue_city',
        'document_issued_at',
        'has_disability',
        'phone',
        'email',
        'address',
        'residence_city',
        'emergency_phone',
        'emergency_contact',
        'collaborator_type',
        'sector',
        'same_cost_center',
        'engagement_type_id',
        'contributor_type',
        'contract_type_id',
        'hired_on',
        'labor_contract_ends_on',
        'left_on',
        'education',
        'marital_status',
        'children_count',
        'eps_code',
        'eps_name',
        'afp_code',
        'afp_name',
        'compensation_fund',
        'arl_name',
        'arl_risk_level',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'birth_date' => 'date',
        'document_issued_at' => 'date',
        'hired_on' => 'date',
        'labor_contract_ends_on' => 'date',
        'left_on' => 'date',
        'age' => 'integer',
        'children_count' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function engagementType()
    {
        return $this->belongsTo(EngagementType::class);
    }

    public function contractType()
    {
        return $this->belongsTo(ContractType::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function assignments()
    {
        return $this->hasMany(WeaponWorkerAssignment::class);
    }

    public function histories()
    {
        return $this->hasMany(WorkerHistory::class);
    }

    public function positionLabel(): string
    {
        return $this->position?->name ?? __('Sin cargo');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }
}
