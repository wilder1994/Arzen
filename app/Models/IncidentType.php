<?php

namespace App\Models;

use App\Support\LegacyCustodyIncidentTypeCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'color',
        'sort_order',
        'requires_modality',
        'requires_attachment',
        'requires_resolution_note',
        'default_status',
        'sla_hours',
        'blocks_operation',
        'persists_operational_block',
        'is_active',
        'is_reportable',
    ];

    protected $casts = [
        'requires_modality' => 'boolean',
        'requires_attachment' => 'boolean',
        'requires_resolution_note' => 'boolean',
        'sla_hours' => 'integer',
        'blocks_operation' => 'boolean',
        'persists_operational_block' => 'boolean',
        'is_active' => 'boolean',
        'is_reportable' => 'boolean',
    ];

    public function modalities()
    {
        return $this->hasMany(IncidentModality::class)->orderBy('sort_order')->orderBy('name');
    }

    public function incidents()
    {
        return $this->hasMany(WeaponIncident::class);
    }

    public function scopeOperationalBlocking(Builder $query): Builder
    {
        return $query->where('blocks_operation', true);
    }

    public function scopeReportable(Builder $query): Builder
    {
        return $query->where('is_reportable', true);
    }

    public function scopeCatalog(Builder $query): Builder
    {
        return $query->whereNotIn('code', LegacyCustodyIncidentTypeCode::codes());
    }

    public function isCatalog(): bool
    {
        return ! LegacyCustodyIncidentTypeCode::isLegacy($this->code);
    }

    public function displayColor(): string
    {
        $color = (string) ($this->color ?: '#1a6572');

        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color) === 1
            ? $color
            : '#1a6572';
    }

    public function contrastOnColor(): string
    {
        $hex = ltrim($this->displayColor(), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $luma = (hexdec(substr($hex, 0, 2)) * 299 + hexdec(substr($hex, 2, 2)) * 587 + hexdec(substr($hex, 4, 2)) * 114) / 1000;

        return $luma > 160 ? '#0f172a' : '#ffffff';
    }

    public function catalogInUse(): bool
    {
        $modalityUses = $this->relationLoaded('modalities')
            ? (int) $this->modalities->sum(static fn (IncidentModality $modality): int => (int) ($modality->incidents_count ?? 0))
            : 0;

        return (int) ($this->incidents_count ?? 0) > 0 || $modalityUses > 0;
    }

    public function syncModalityRequirement(): void
    {
        $this->update([
            'requires_modality' => $this->modalities()->where('is_active', true)->exists(),
        ]);
    }

    public function scopeNonReportable(Builder $query): Builder
    {
        return $query->where('is_reportable', false);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}