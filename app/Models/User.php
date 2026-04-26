<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Tenant;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'branch_id',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    /**
     * Relación con el tenant (empresa)
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relación con la sucursal
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Obtiene los roles del usuario (relación many-to-many)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Obtiene el primer rol del usuario (para compatibilidad)
     */
    public function role()
    {
        return $this->roles()->first();
    }

    /**
     * Verifica si el usuario tiene un rol específico
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Verifica si es administrador global (root)
     * ID 1 = root (administrador general)
     */
    public function isRoot(): bool
    {
        return $this->hasRole('root') || $this->roles()->where('roles.id', 1)->exists();
    }

    /**
     * Verifica si es administrador de sucursal
     * ID 2 = administrador (admin de sucursal)
     */
    public function isBranchAdmin(): bool
    {
        return $this->hasRole('administrador') || $this->roles()->where('roles.id', 2)->exists();
    }

    /**
     * Verifica si es trabajador
     * ID 3 = trabajador
     */
    public function isWorker(): bool
    {
        return $this->hasRole('trabajador') || $this->roles()->where('roles.id', 3)->exists();
    }

    /**
     * Verifica si es cliente
     * ID 4 = cliente
     */
    public function isClient(): bool
    {
        return $this->hasRole('cliente') || $this->roles()->where('roles.id', 4)->exists();
    }

    /**
     * Verifica si necesita tener sucursal asignada
     */
    public function needsBranch(): bool
    {
        // Root no necesita sucursal, todos los demás sí
        return !$this->isRoot();
    }

    /**
     * Verifica si tiene acceso al sistema (tiene sucursal asignada o es root)
     */
    public function hasSystemAccess(): bool
    {
        return $this->isRoot() || ($this->branch_id !== null);
    }

    /**
     * Scope para obtener solo usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para obtener usuarios de una sucursal específica
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Configuración de Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'tenant_id', 'branch_id', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Usuario {$eventName}");
    }
}
