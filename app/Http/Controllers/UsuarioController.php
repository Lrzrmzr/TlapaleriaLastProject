<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['roles', 'branch'])->get()->map(function ($usuario) {
            $primerRol = $usuario->roles->first();
            return [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'role' => $primerRol ? [
                    'id' => $primerRol->id,
                    'name' => $primerRol->name,
                ] : null,
                'branch' => $usuario->branch ? [
                    'id' => $usuario->branch->id,
                    'name' => $usuario->branch->name,
                    'code' => $usuario->branch->code,
                ] : null,
                'active' => $usuario->active,
                'created_at' => $usuario->created_at->format('d/m/Y'),
                'email_verified' => $usuario->email_verified_at !== null,
            ];
        });

        $roles = Role::all();
        $branches = Branch::where('active', true)->orderBy('name')->get();
        $user = User::with('roles')->find(Auth::id());
        $userRole = $user->roles->first();

        // Estadísticas
        $totalUsuarios = User::count();
        $usuariosConRol = User::has('roles')->count();
        $usuariosSinRol = User::doesntHave('roles')->count();
        $usuariosVerificados = User::whereNotNull('email_verified_at')->count();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'branches' => $branches,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $userRole ? [
                    'id' => $userRole->id,
                    'name' => $userRole->name,
                ] : null,
            ],
            'stats' => [
                'total' => $totalUsuarios,
                'conRol' => $usuariosConRol,
                'sinRol' => $usuariosSinRol,
                'verificados' => $usuariosVerificados,
            ]
        ]);
    }

    public function asignarRol(Request $request)
    {
        $user = User::with('roles')->find(Auth::id());
        $userRole = $user->roles->first();

        if ($user->id !== 1 && ($userRole->name ?? null) !== 'administrador') {
            abort(403, 'Solo el administrador puede asignar roles');
        }

        $usuario = User::findOrFail($request->usuario_id);

        // Remover roles existentes
        $usuario->roles()->detach();

        // Asignar nuevo rol si se proporcionó
        if ($request->rol_id) {
            $usuario->roles()->attach($request->rol_id);
        }

        return redirect()->back();
    }

    public function asignarSucursal(Request $request, User $user)
    {
        $currentUser = User::with('roles')->find(Auth::id());

        // Solo root o administrador pueden asignar sucursales
        if (!$currentUser->isRoot() && !$currentUser->isBranchAdmin()) {
            abort(403, 'No tienes permisos para asignar sucursales');
        }

        $request->validate([
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $branchId = $request->branch_id;

        // Si se está asignando una sucursal
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);

            // Verificar si el usuario que se está asignando es administrador (ID=2)
            $userRoles = $user->roles;
            $isAdminRole = $userRoles->contains('id', 2) || $userRoles->contains('name', 'administrador');

            if ($isAdminRole) {
                // Verificar que la sucursal no tenga ya un administrador
                // Buscar usuarios de esta sucursal que tengan el rol de administrador (ID=2)
                $existingAdmin = User::where('users.branch_id', $branchId)
                    ->where('users.id', '!=', $user->id)
                    ->join('role_user', 'users.id', '=', 'role_user.user_id')
                    ->where('role_user.role_id', 2)
                    ->select('users.*')
                    ->first();

                if ($existingAdmin) {
                    return redirect()->back()->withErrors([
                        'error' => "La sucursal '{$branch->name}' ya tiene un administrador asignado ({$existingAdmin->name}). Solo puede haber un administrador por sucursal."
                    ]);
                }
            }

            // Administradores de sucursal solo pueden asignar a su propia sucursal
            if ($currentUser->isBranchAdmin() && !$currentUser->isRoot()) {
                if ($currentUser->branch_id != $branchId) {
                    abort(403, 'Solo puedes asignar usuarios a tu propia sucursal');
                }
            }
        }

        // Asignar o remover sucursal
        $user->branch_id = $branchId;
        $user->save();

        return redirect()->back()->with('success', $branchId
            ? 'Sucursal asignada correctamente'
            : 'Sucursal removida correctamente'
        );
    }
}
