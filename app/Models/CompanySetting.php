<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CompanySetting extends Model
{
    public const CACHE_KEY = 'company_settings.current';

    public const DEFAULT_PREFIX = 'ARM-';

    public const FALLBACK_LOGO = 'images/Logo.png';

    protected $fillable = [
        'legal_name',
        'nit',
        'address',
        'city',
        'phone',
        'email',
        'website',
        'legal_rep_name',
        'legal_rep_document',
        'legal_rep_document_city',
        'agent_name',
        'agent_document',
        'agent_document_city',
        'internal_code_prefix',
        'logo_file_id',
        'letterhead_file_id',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        try {
            $id = Cache::rememberForever(self::CACHE_KEY, function () {
                return Schema::hasTable('company_settings')
                    ? static::query()->value('id')
                    : null;
            });
        } catch (Throwable) {
            $id = null;
        }

        $settings = $id ? static::query()->with(['logoFile', 'letterheadFile'])->find($id) : null;

        return $settings ?? new static(['internal_code_prefix' => self::DEFAULT_PREFIX]);
    }

    public static function firstOrCreateSingleton(): self
    {
        return static::query()->first()
            ?? static::query()->create(['internal_code_prefix' => self::DEFAULT_PREFIX]);
    }

    public function logoFile()
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    public function letterheadFile()
    {
        return $this->belongsTo(File::class, 'letterhead_file_id');
    }

    public function logoPath(): string
    {
        $file = $this->logoFile;

        if ($file && Storage::disk($file->disk)->exists($file->path)) {
            return Storage::disk($file->disk)->path($file->path);
        }

        return public_path(self::FALLBACK_LOGO);
    }

    public function logoUrl(): string
    {
        return $this->logoFile
            ? route('company.logo', ['v' => $this->logo_file_id])
            : asset(self::FALLBACK_LOGO);
    }

    public function codePrefix(): string
    {
        return (string) ($this->internal_code_prefix ?? '');
    }

    public function agentName(): ?string
    {
        return $this->agent_name ?: $this->legal_rep_name;
    }

    public function agentDocument(): ?string
    {
        return $this->agent_name ? $this->agent_document : $this->legal_rep_document;
    }

    public function agentDocumentCity(): ?string
    {
        return $this->agent_name ? $this->agent_document_city : $this->legal_rep_document_city;
    }

    /**
     * @return list<string>
     */
    public function missingLetterFields(): array
    {
        $required = [
            'legal_name' => __('Razón social'),
            'nit' => __('NIT'),
            'city' => __('Ciudad'),
            'legal_rep_name' => __('Nombre del representante legal'),
            'legal_rep_document' => __('Cédula del representante legal'),
            'legal_rep_document_city' => __('Ciudad de expedición de la cédula'),
        ];

        return collect($required)
            ->filter(fn (string $label, string $field) => blank($this->{$field}))
            ->values()
            ->all();
    }
}
