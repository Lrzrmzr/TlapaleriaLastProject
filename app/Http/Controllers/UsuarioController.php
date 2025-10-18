<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('roles')->get()->map(function ($usuario) {
            $primerRol = $usuario->roles->first();
            return [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'role' => $primerRol ? [
                    'id' => $primerRol->id,
                    'name' => $primerRol->name,
                ] : null,
                'created_at' => $usuario->created_at->format('d/m/Y'),
                'email_verified' => $usuario->email_verified_at !== null,
            ];
        });

        $roles = Role::all();
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
}
