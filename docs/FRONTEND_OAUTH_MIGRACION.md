# Migración del Frontend a OAuth 2.0

## Resumen

El frontend de Ferretería TOTORO ha sido migrado para utilizar OAuth 2.0 (Laravel Passport) para autenticación, manteniendo compatibilidad con Inertia.js.

## Cambios Implementados

### 1. Composable de Autenticación (`resources/js/composables/useAuth.js`)

Se creó un composable Vue 3 que maneja toda la lógica de autenticación OAuth 2.0:

**Funcionalidades:**
- ✅ Gestión de tokens en `localStorage`
- ✅ Login con OAuth 2.0
- ✅ Logout (revoca token y cierra sesión)
- ✅ Verificación de autenticación
- ✅ Verificación de roles (root, administrador, trabajador)
- ✅ Configuración automática de headers de Axios

**Métodos disponibles:**
```javascript
const {
  login,        // Autenticar usuario
  logout,       // Cerrar sesión
  isAuthenticated, // Verificar si está autenticado
  hasRole,      // Verificar si tiene un rol
  user,         // Datos del usuario autenticado
  token         // Access token actual
} = useAuth();
```

### 2. Interceptores de Axios (`resources/js/app.js`)

Se configuraron interceptores para:

**Request Interceptor:**
- Inyecta automáticamente el Bearer token en todas las peticiones
- Lee el token de `localStorage`
- Agrega header: `Authorization: Bearer {token}`

**Response Interceptor:**
- Detecta errores 401 (No autenticado)
- Cierra sesión automáticamente si el token expiró
- Redirige al login

### 3. Componente de Login Actualizado (`resources/js/Pages/Auth/Login.vue`)

**Cambios:**
- ❌ Ya NO usa Inertia form helper
- ✅ Ahora llama directamente a `/api/login`
- ✅ Usa el composable `useAuth` para login
- ✅ Maneja errores OAuth (401, 403, 422)
- ✅ Muestra indicador "🔐 Autenticación segura con OAuth 2.0"

**Flujo:**
1. Usuario ingresa email y contraseña
2. Se llama a `POST /api/login`
3. Backend valida credenciales y roles
4. Backend genera JWT token y crea sesión web
5. Frontend guarda token en localStorage
6. Frontend redirige a `/dashboard`

### 4. Layout Autenticado Actualizado (`resources/js/Layouts/AuthenticatedLayout.vue`)

**Cambios:**
- ✅ Usa `useAuth()` para obtener datos del usuario
- ✅ Implementa logout con OAuth 2.0
- ✅ Verifica roles usando `hasRole('root')`
- ✅ Botón de logout llama a `/api/logout`

**Flujo de Logout:**
1. Usuario hace clic en "Log Out"
2. Se llama a `POST /api/logout`
3. Backend revoca el token JWT
4. Backend cierra la sesión web
5. Frontend limpia localStorage
6. Frontend redirige a `/login`

### 5. Backend: Autenticación Híbrida (`app/Http/Controllers/Api/AuthController.php`)

**Innovación Clave:**
Para mantener compatibilidad con Inertia.js (que requiere sesiones), se implementó un sistema híbrido:

**Al hacer Login:**
```php
// 1. Genera token OAuth 2.0
$token = $user->createToken('Personal Access Token')->accessToken;

// 2. También autentica en sesión web
Auth::login($user);

// 3. Retorna ambos
return response()->json([
    'access_token' => $token, // Para API calls
    'user' => $userData        // Para frontend
]);
```

**Al hacer Logout:**
```php
// 1. Revoca token OAuth
$request->user()->token()->revoke();

// 2. Cierra sesión web
Auth::logout();
```

## Arquitectura Final

### Flujo de Autenticación

```
1. Login Page (Vue)
   ↓
2. POST /api/login (OAuth 2.0)
   ↓
3. Backend valida y genera:
   - JWT Token (OAuth 2.0)
   - Sesión Web (Inertia)
   ↓
4. Frontend recibe:
   - Token → localStorage
   - User data → localStorage
   ↓
5. Navegación Inertia:
   - Usa sesión web (cookies)
   ↓
6. API Calls:
   - Usa Bearer token (headers)
```

### Ventajas de esta Arquitectura

1. **Compatibilidad Total con Inertia:**
   - Las páginas Inertia funcionan con sesiones web
   - No requiere pasar tokens en cada request de Inertia

2. **OAuth 2.0 para API:**
   - Todos los endpoints `/api/*` usan Bearer tokens
   - Tokens JWT firmados digitalmente
   - Expiración automática en 15 días

3. **Mejor Seguridad:**
   - Tokens imposibles de falsificar
   - Revocación inmediata de tokens
   - Control de expiración

4. **Preparado para Móvil:**
   - La API OAuth 2.0 funciona independientemente
   - Apps móviles pueden usar solo los endpoints `/api/*`
   - No dependen de cookies/sesiones

## Endpoints de Autenticación

### POST /api/login
**Body:**
```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña"
}
```

**Respuesta (200):**
```json
{
  "message": "Autenticación exitosa",
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 1296000,
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@ejemplo.com",
    "roles": ["root"]
  }
}
```

### POST /api/logout
**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "message": "Sesión cerrada exitosamente. El token ha sido revocado."
}
```

### GET /api/me
**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@ejemplo.com",
    "roles": ["root"]
  },
  "token_info": {
    "scopes": [],
    "expires_at": "2024-01-16T00:00:00.000000Z",
    "revoked": false
  }
}
```

## Uso del Composable useAuth

### En cualquier componente Vue:

```vue
<script setup>
import { useAuth } from '@/composables/useAuth';

const { user, isAuthenticated, hasRole, logout } = useAuth();

// Verificar si está autenticado
if (isAuthenticated.value) {
  console.log('Usuario autenticado:', user.value.name);
}

// Verificar rol
if (hasRole('root')) {
  console.log('Usuario es root');
}

// Cerrar sesión
const handleLogout = async () => {
  await logout();
  // Redirige automáticamente a /login
};
</script>

<template>
  <div v-if="isAuthenticated">
    <p>Bienvenido {{ user?.name }}</p>
    <button @click="handleLogout">Cerrar Sesión</button>
  </div>
</template>
```

## Compatibilidad con Rutas Web

Las rutas web (`routes/web.php`) mantienen el middleware `auth` estándar:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // ... otras rutas
});
```

**¿Por qué funciona?**
- El login OAuth también crea una sesión web con `Auth::login($user)`
- Inertia funciona normalmente con sesiones
- Los componentes Vue pueden usar tanto la sesión (Inertia props) como el token (para API calls)

## Próximos Pasos

Para completar la migración:

1. ✅ **Probar el flujo completo:**
   - Login con credenciales válidas
   - Acceder al dashboard
   - Navegar entre páginas
   - Hacer logout

2. **Opcional - Manejo de "Remember Me":**
   - Actualmente el checkbox está en el formulario
   - Se puede implementar tokens de mayor duración

3. **Opcional - Refresh Token:**
   - Renovar tokens antes de que expiren
   - Usar endpoint `POST /oauth/token` con `grant_type=refresh_token`

## Notas Importantes

1. **Página Pública:**
   - La página pública (`/`) NO requiere autenticación
   - Sigue funcionando normalmente sin cambios

2. **Roles Válidos:**
   - `root` - Super administrador (acceso total)
   - `administrador` - Administrador
   - `trabajador` - Empleado
   - `cliente` - Solo acceso público (NO puede autenticarse en admin)

3. **Expiración de Tokens:**
   - Access tokens: 15 días
   - Refresh tokens: 30 días
   - Personal access tokens: 6 meses

4. **localStorage:**
   - Se usa para persistir tokens entre recargas
   - Es seguro para aplicaciones internas
   - Para apps públicas, considerar otras opciones

---

**Última actualización:** Diciembre 2025
