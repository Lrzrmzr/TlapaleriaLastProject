<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware ResolveTenant
 *
 * Identifica el tenant activo en cada petición.
 *
 * Estrategias de resolución (en orden):
 *  1. Subdominio: ferreteria-garcia.totoro.mx → slug "ferreteria-garcia"
 *  2. Header X-Tenant-ID: para la app móvil (Passport) y desarrollo por IP
 *
 * Si no se resuelve ningún tenant → contexto central (root access).
 * Si el tenant existe pero está suspendido → 403/redirect.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            // Dominio central o sin contexto de tenant → pasar sin filtro (root)
            return $next($request);
        }

        $tenant = Tenant::withoutTenant()->find($tenantId);

        if (!$tenant) {
            return $this->tenantNotFound($request);
        }

        if ($tenant->isSuspended()) {
            return $this->tenantSuspended($request, $tenant);
        }

        // Inicializar stancl/tenancy para que currentTenantId() lo detecte
        tenancy()->initialize($tenant);

        // Guardar en contenedor para acceso directo si se necesita
        app()->instance('currentTenant', $tenant);

        $response = $next($request);

        // Limpiar contexto de tenant al terminar la petición
        tenancy()->end();

        return $response;
    }

    private function resolveTenantId(Request $request): ?string
    {
        // 1. Intentar resolver desde subdominio
        $slugFromSubdomain = $this->resolveFromSubdomain($request);
        if ($slugFromSubdomain !== null) {
            return $slugFromSubdomain;
        }

        // 2. Fallback: header X-Tenant-ID (app móvil o desarrollo por IP)
        $header = $request->header('X-Tenant-ID');
        if (!empty($header)) {
            return $header;
        }

        return null;
    }

    private function resolveFromSubdomain(Request $request): ?string
    {
        $host          = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        foreach ($centralDomains as $centralDomain) {
            // Es el dominio central exacto → sin tenant
            if ($host === $centralDomain) {
                return null;
            }

            // Es un subdominio del dominio central → extraer slug
            if (str_ends_with($host, '.' . $centralDomain)) {
                $subdomain = substr($host, 0, strlen($host) - strlen('.' . $centralDomain));

                // Ignorar subdominios reservados
                if (in_array($subdomain, ['www', 'api', 'admin', 'mail', 'ftp'])) {
                    return null;
                }

                return $subdomain;
            }
        }

        return null;
    }

    private function tenantNotFound(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Empresa no encontrada.',
                'error'   => 'tenant_not_found',
            ], 404);
        }

        abort(404, 'La empresa que buscas no existe o fue dada de baja.');
    }

    private function tenantSuspended(Request $request, Tenant $tenant): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Suscripción suspendida. Contacta al administrador de ' . $tenant->name . '.',
                'error'   => 'tenant_suspended',
            ], 403);
        }

        abort(403, 'La suscripción de ' . $tenant->name . ' está suspendida.');
    }
}
