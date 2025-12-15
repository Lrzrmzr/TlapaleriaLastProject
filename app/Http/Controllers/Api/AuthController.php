<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login con OAuth 2.0 (Password Grant)
     * Genera access_token y refresh_token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validar credenciales
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // Verificar credenciales
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas son incorrectas.'
            ], 401);
        }

        // Verificar rol (root, administrador o trabajador tienen acceso)
        $rolesPermitidos = ['root', 'administrador', 'trabajador'];
        $tieneRolValido = false;

        foreach ($rolesPermitidos as $rol) {
            if ($user->hasRole($rol)) {
                $tieneRolValido = true;
                break;
            }
        }

        if (!$tieneRolValido) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a la API. Solo usuarios autorizados pueden autenticarse.'
            ], 403);
        }

        // Crear token personal de acceso con Passport
        // Passport genera automáticamente un access_token (JWT) con expiración
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->accessToken;

        // Autenticar al usuario en la sesión web para compatibilidad con Inertia
        Auth::guard('web')->login($user);

        // Regenerar la sesión para prevenir ataques de fijación de sesión
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Autenticación exitosa',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => now()->addDays(15)->diffInSeconds(now()), // Segundos hasta expiración
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')
            ]
        ], 200);
    }

    /**
     * Refresh Token
     * Obtiene un nuevo access_token usando el refresh_token
     *
     * Nota: Para usar esta funcionalidad, el cliente debe implementar
     * el flujo completo de OAuth 2.0 usando el endpoint /oauth/token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // Este endpoint es informativo
        // En OAuth 2.0, el refresh se hace directamente con POST /oauth/token
        return response()->json([
            'message' => 'Para renovar el token, usa POST /oauth/token con grant_type=refresh_token',
            'documentation' => [
                'endpoint' => 'POST /oauth/token',
                'body' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => 'el_refresh_token_que_obtuviste_en_login',
                    'client_id' => 'tu_client_id',
                    'client_secret' => 'tu_client_secret',
                ]
            ]
        ], 200);
    }

    /**
     * Logout - Revoca el token actual
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Obtener el token actual y revocarlo
        $request->user()->token()->revoke();

        // Cerrar sesión web explícitamente en el guard 'web'
        Auth::guard('web')->logout();

        // Invalidar sesión completamente
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente. El token ha sido revocado.'
        ], 200);
    }

    /**
     * Logout de todos los dispositivos - Revoca todos los tokens del usuario
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAll(Request $request)
    {
        // Revocar todos los tokens del usuario
        $user = $request->user();

        $user->tokens->each(function ($token) {
            $token->revoke();
        });

        // Cerrar sesión web explícitamente en el guard 'web'
        Auth::guard('web')->logout();

        // Invalidar sesión completamente
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Se han cerrado todas las sesiones exitosamente. Todos los tokens han sido revocados.'
        ], 200);
    }

    /**
     * Información del usuario autenticado
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $token = $request->user()->token();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at,
            ],
            'token_info' => [
                'scopes' => $token->scopes,
                'expires_at' => $token->expires_at,
                'revoked' => $token->revoked
            ]
        ], 200);
    }
}
