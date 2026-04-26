<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario tenga acceso a los datos de la sucursal
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado, continuar (el middleware auth se encargará)
        if (!$user) {
            return $next($request);
        }

        // Clientes (ID=4) NO tienen acceso al sistema actualmente (para futuras ventas en línea)
        if ($user->isClient()) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Los clientes no tienen acceso al sistema de administración. Este rol está reservado para futuras ventas en línea.']);
        }

        // Root (ID=1) tiene acceso a todo sin necesidad de sucursal ni tenant
        if ($user->isRoot()) {
            $request->merge(['user_branch_id' => null]); // null = ve todas las sucursales
            $request->merge(['is_root' => true]);
            $request->merge(['user_tenant_id' => null]); // null = ve todos los tenants
            return $next($request);
        }

        // Usuarios no-root deben pertenecer a un tenant activo
        if (!$user->tenant_id || !$user->tenant || !$user->tenant->isActive()) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Tu empresa no tiene una suscripción activa. Contacta al administrador.']);
        }

        // Administradores (ID=2) y Trabajadores (ID=3) NECESITAN sucursal asignada
        if (!$user->hasSystemAccess()) {
            // Redirigir a página especial de "Sin Sucursal Asignada"
            return redirect()->route('sin-sucursal')
                ->withErrors(['error' => 'No tienes una sucursal asignada. Contacta al administrador para que te asigne una sucursal.']);
        }

        // Validar que la sucursal del usuario pertenece al mismo tenant
        if ($user->branch && $user->branch->tenant_id !== $user->tenant_id) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Acceso inválido: sucursal no pertenece a tu empresa.']);
        }

        // Agregar la sucursal y tenant del usuario al request para uso posterior
        $request->merge(['user_branch_id' => $user->branch_id]);
        $request->merge(['user_tenant_id' => $user->tenant_id]);
        $request->merge(['is_root' => false]);

        return $next($request);
    }
}
