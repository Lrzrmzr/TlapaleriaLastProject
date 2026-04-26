<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait BelongsToTenant
 *
 * Aplica aislamiento automático de datos por tenant en modo single-database.
 *
 * - Inyecta tenant_id automáticamente al crear registros.
 * - Aplica un Global Scope que filtra todos los queries por tenant_id.
 * - El usuario root (tenant_id = null) ve datos de todos los tenants.
 * - Usar withoutTenant() para bypass explícito (ej. reportes cross-tenant del root).
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // 1. Global Scope: filtra automáticamente todos los queries por tenant activo
        static::addGlobalScope(new TenantScope());

        // 2. Auto-inyectar tenant_id al crear un registro
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = currentTenantId();
            }
        });
    }

    /**
     * Escape hatch: omite el filtro de tenant.
     * Usar únicamente en contextos del super admin (root) o scripts de migración.
     *
     * Ejemplo: Branch::withoutTenant()->get()
     */
    public static function withoutTenant(): Builder
    {
        return static::withoutGlobalScope(TenantScope::class);
    }

    /**
     * Scope para filtrar explícitamente por un tenant específico.
     * Útil en comandos de consola o reportes cross-tenant del root.
     *
     * Ejemplo: Sale::forTenant('ferreteria-garcia')->get()
     */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class)
                     ->where($this->getTable() . '.tenant_id', $tenantId);
    }
}
