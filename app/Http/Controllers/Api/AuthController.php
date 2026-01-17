<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login de usuario y generación de token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Intentar autenticación (esto también crea la sesión web)
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $user = Auth::user();

        // Regenerar sesión para seguridad
        $request->session()->regenerate();

        // Crear token de acceso con Passport (para app móvil)
        $token = $user->createToken('mobile-app')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()?->name ?? null,
                    'branch_id' => $user->branch_id,
                    'branch' => $user->branch ? [
                        'id' => $user->branch->id,
                        'name' => $user->branch->name,
                        'code' => $user->branch->code,
                    ] : null,
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? null,
                'branch_id' => $user->branch_id,
                'branch' => $user->branch ? [
                    'id' => $user->branch->id,
                    'name' => $user->branch->name,
                    'code' => $user->branch->code,
                ] : null,
            ]
        ]);
    }

    /**
     * Logout - revocar token y cerrar sesión web
     */
    public function logout(Request $request)
    {
        // Revocar token de Passport (para app móvil)
        if ($request->user() && method_exists($request->user(), 'token')) {
            $request->user()->token()->revoke();
        }

        // Cerrar sesión web (invalidar sesión)
        Auth::guard('web')->logout();

        // Invalidar sesión y regenerar token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }
}
