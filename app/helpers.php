<?php

/**
 * Retorna el tenant_id activo en la petición actual.
 *
 * Resolución en cascada:
 *  1. Tenant inicializado por stancl/tenancy (subdomain middleware)
 *  2. Usuario autenticado (web o API)
 *  3. null → root o contexto sin tenant (sin filtro en TenantScope)
 */
function currentTenantId(): ?string
{
    // 1. Tenant resuelto por stancl via subdominio
    try {
        $tenancy = tenancy();
        if ($tenancy->initialized && $tenancy->tenant !== null) {
            return (string) $tenancy->tenant->getTenantKey();
        }
    } catch (\Throwable) {
        // tenancy() no disponible aún (ej. migraciones, seeders)
    }

    // 2. Usuario autenticado (web session o Passport API token)
    $user = auth()->user() ?? auth('api')->user();
    if ($user) {
        return $user->tenant_id; // null si es root
    }

    return null;
}

/**
 * Retorna la instancia del Tenant activo, o null si es root/sin contexto.
 */
function currentTenant(): ?\App\Models\Tenant
{
    $tenantId = currentTenantId();

    if ($tenantId === null) {
        return null;
    }

    return \App\Models\Tenant::find($tenantId);
}
