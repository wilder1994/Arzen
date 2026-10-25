<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Access\ModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'ADMIN';

    public const ROLE_RESPONSABLE = 'RESPONSABLE';

    public const ROLE_AUDITOR = 'AUDITOR';

    public const ROLE_ALMACEN = 'ALMACEN';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
        'role',
        'position_id',
        'is_active',
        'cost_center',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    /**
     * @var array<string, string>|null
     */
    private ?array $moduleLevelsCache = null;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'must_change_password' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function moduleGrants()
    {
        return $this->hasMany(UserModuleGrant::class);
    }

    /**
     * Rol con nombre visible, igual que en Zentra.
     *
     * @return array<string, string>
     */
    public static function roleLabels(): array
    {
        return [
            self::ROLE_ADMIN => __('Súper administrador'),
            self::ROLE_RESPONSABLE => __('Responsable'),
            self::ROLE_AUDITOR => __('Auditor'),
            self::ROLE_ALMACEN => __('Almacén (Chalecos)'),
        ];
    }

    public function roleLabel(): string
    {
        return self::roleLabels()[$this->role] ?? (string) $this->role;
    }

    /**
     * Matriz efectiva: la guardada para el usuario o, si no tiene, la del rol.
     *
     * @return array<string, string>
     */
    public function moduleLevels(): array
    {
        if ($this->isAdmin()) {
            return ModuleAccess::defaultsForRole(self::ROLE_ADMIN);
        }

        if ($this->moduleLevelsCache !== null) {
            return $this->moduleLevelsCache;
        }

        $levels = ModuleAccess::defaultsForRole($this->role);
        $grants = $this->relationLoaded('moduleGrants') ? $this->moduleGrants : $this->moduleGrants()->get();

        if ($grants->isNotEmpty()) {
            $levels = array_fill_keys(ModuleAccess::keys(), ModuleAccess::NONE);

            foreach ($grants as $grant) {
                if (array_key_exists($grant->module, $levels) && ModuleAccess::isValidLevel($grant->level)) {
                    $levels[$grant->module] = $grant->level;
                }
            }
        }

        return $this->moduleLevelsCache = $levels;
    }

    public function moduleLevel(string $module): string
    {
        return $this->moduleLevels()[$module] ?? ModuleAccess::NONE;
    }

    public function canViewModule(string $module): bool
    {
        return $this->moduleLevel($module) !== ModuleAccess::NONE;
    }

    public function canManageModule(string $module): bool
    {
        return $this->moduleLevel($module) === ModuleAccess::MANAGE;
    }

    /**
     * @param  array<string, string>  $levels
     */
    public function syncModuleLevels(array $levels): void
    {
        $this->moduleGrants()->delete();

        foreach (ModuleAccess::keys() as $module) {
            $level = $levels[$module] ?? ModuleAccess::NONE;

            if ($level !== ModuleAccess::NONE && ModuleAccess::isValidLevel($level)) {
                $this->moduleGrants()->create(['module' => $module, 'level' => $level]);
            }
        }

        $this->moduleGrants()->firstOrCreate(['module' => '_matrix'], ['level' => 'set']);
        $this->moduleLevelsCache = null;
        $this->unsetRelation('moduleGrants');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'user_clients')->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isResponsible(): bool
    {
        return $this->role === self::ROLE_RESPONSABLE;
    }

    public function isAuditor(): bool
    {
        return $this->role === self::ROLE_AUDITOR;
    }

    public function isAlmacen(): bool
    {
        return $this->role === self::ROLE_ALMACEN;
    }

    public function canAccessVestModule(): bool
    {
        return $this->isAdmin()
            || $this->isResponsible()
            || $this->isAuditor()
            || $this->isAlmacen();
    }

    public function hasGlobalVestScope(): bool
    {
        return $this->isAdmin() || $this->isAuditor() || $this->isAlmacen();
    }

    public function canManageAllVests(): bool
    {
        return $this->isAdmin() || $this->isAlmacen();
    }

    /**
     * Antiguo nivel de responsabilidad, derivado de la matriz: Gestionar armas = 1, Ver armas = 2.
     */
    public function responsibilityLevelNumber(): ?int
    {
        return match ($this->moduleLevel('weapons')) {
            ModuleAccess::MANAGE => 1,
            ModuleAccess::VIEW => 2,
            default => null,
        };
    }

    public function isResponsibleLevelOne(): bool
    {
        return $this->isResponsible() && $this->responsibilityLevelNumber() === 1;
    }

    public function hasClientInPortfolio(int $clientId): bool
    {
        return $this->clients()->whereKey($clientId)->exists();
    }

    /**
     * Responsable válido para custodia/taller en un cliente: nivel 1 con cartera o ADMIN con cartera.
     */
    public function isCustodyResponsibleForClient(int $clientId): bool
    {
        if ($clientId <= 0 || ! $this->hasClientInPortfolio($clientId)) {
            return false;
        }

        return $this->isResponsibleLevelOne() || $this->isAdmin();
    }

    public function isResponsibleReadOnly(): bool
    {
        return $this->isResponsible() && $this->responsibilityLevelNumber() === 2;
    }

    public function isReadOnlyOperator(): bool
    {
        return $this->isAuditor() || $this->isResponsibleReadOnly();
    }
}

