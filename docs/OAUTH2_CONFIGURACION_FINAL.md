# Configuración OAuth 2.0 - Sistema Completo

## Resumen Ejecutivo

El sistema de Ferretería TOTORO ha sido completamente migrado de Laravel Sanctum a **Laravel Passport (OAuth 2.0)** con autenticación híbrida que combina:
- **JWT Tokens** para API calls
- **Sesiones Web** para compatibilidad con Inertia.js

✅ **Estado**: Completamente funcional y probado

---

## 🔧 Configuración Backend

### 1. Dependencias Instaladas

**Composer.json:**
```json
{
  "require": {
    "laravel/passport": "^12.3"
  }
}
```

**Instalación:**
```bash
composer require laravel/passport
php artisan migrate
php artisan passport:install
```

### 2. Modelo User

**[app/Models/User.php](../app/Models/User.php)**

```php
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    // Métodos de roles
    public function roles() { ... }
    public function hasRole($roleName) { ... }
}
```

✅ **Verificado**: Usa `Laravel\Passport\HasApiTokens` (NO Sanctum)

### 3. Configuración de Passport

**[app/Providers/AppServiceProvider.php](../app/Providers/AppServiceProvider.php)**

```php
use Laravel\Passport\Passport;

public function boot(): void
{
    // Expiración de tokens
    Passport::tokensExpireIn(now()->addDays(15));           // Access tokens: 15 días
    Passport::refreshTokensExpireIn(now()->addDays(30));    // Refresh tokens: 30 días
    Passport::personalAccessTokensExpireIn(now()->addMonths(6)); // Personal tokens: 6 meses

    // Habilitar Password Grant
    Passport::enablePasswordGrant();
}
```

### 4. Guards de Autenticación

**[config/auth.php](../config/auth.php)**

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'passport',  // ← OAuth 2.0
        'provider' => 'users',
        'hash' => false,
    ],
],
```

✅ **Verificado**: Guard API usa `passport` (NO sanctum)

### 5. AuthController - Autenticación Híbrida

**[app/Http/Controllers/Api/AuthController.php](../app/Http/Controllers/Api/AuthController.php)**

**Login (Líneas 20-77):**
```php
public function login(Request $request)
{
    // 1. Validar credenciales
    $user = User::where('email', $request->email)->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciales incorrectas'], 401);
    }

    // 2. Verificar roles (root, administrador, trabajador)
    $rolesPermitidos = ['root', 'administrador', 'trabajador'];
    if (!$user->hasRole(...)) {
        return response()->json(['message' => 'Sin permisos'], 403);
    }

    // 3. Generar token OAuth 2.0
    $token = $user->createToken('Personal Access Token')->accessToken;

    // 4. IMPORTANTE: Crear sesión web también
    Auth::guard('web')->login($user);
    $request->session()->regenerate();

    // 5. Retornar token + información del usuario
    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'expires_in' => ...,
        'user' => [...]
    ]);
}
```

**Logout (Líneas 109-124):**
```php
public function logout(Request $request)
{
    // 1. Revocar token OAuth
    $request->user()->token()->revoke();

    // 2. Cerrar sesión web
    Auth::guard('web')->logout();

    // 3. Invalidar sesión completamente
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'Sesión cerrada']);
}
```

**Métodos Disponibles:**
- `POST /api/login` - Autenticación
- `POST /api/logout` - Cerrar sesión actual
- `POST /api/logout-all` - Cerrar todas las sesiones
- `GET /api/me` - Información del usuario autenticado
- `GET /api/refresh-info` - Info sobre refresh tokens

### 6. Rutas API

**[routes/api.php](../routes/api.php)**

```php
// Públicas (sin auth)
Route::get('/products', [PublicController::class, 'apiProducts']);

// Login (requiere middleware 'web' para sesión)
Route::middleware(['web'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protegidas (requieren OAuth + sesión web)
Route::middleware(['auth:api', 'web'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas administrativas (con roles)
    Route::middleware(['role:root,administrador,trabajador'])->group(function () {
        Route::apiResource('admin/products', ProductController::class);
        Route::apiResource('admin/suppliers', SupplierController::class);
        // ... más rutas
    });
});
```

✅ **Clave**: Middleware `web` agregado para tener acceso a sesiones

### 7. Middleware de Roles

**[app/Http/Middleware/CheckRole.php](../app/Http/Middleware/CheckRole.php)**

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!$request->user()) {
        return response()->json(['message' => 'No autenticado'], 401);
    }

    foreach ($roles as $role) {
        if ($request->user()->hasRole($role)) {
            return $next($request);
        }
    }

    return response()->json(['message' => 'Sin permisos'], 403);
}
```

**Roles del Sistema:**
- `root` - Super administrador (acceso total)
- `administrador` - Administrador
- `trabajador` - Empleado
- `cliente` - Cliente (solo acceso público)

---

## 🎨 Configuración Frontend

### 1. Composable useAuth

**[resources/js/composables/useAuth.js](../resources/js/composables/useAuth.js)**

```javascript
import { ref, computed } from 'vue';
import axios from 'axios';

const token = ref(localStorage.getItem('access_token') || null);
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));

export function useAuth() {
    const isAuthenticated = computed(() => !!token.value);

    const login = async (credentials) => {
        const response = await axios.post('/api/login', credentials);
        const { access_token, user: userData } = response.data;

        // Guardar en localStorage
        token.value = access_token;
        user.value = userData;
        localStorage.setItem('access_token', access_token);
        localStorage.setItem('user', JSON.stringify(userData));

        // Configurar header de Axios
        axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;

        return response.data;
    };

    const logout = async () => {
        if (token.value) {
            await axios.post('/api/logout', {}, {
                headers: { 'Authorization': `Bearer ${token.value}` }
            });
        }

        // Limpiar estado
        token.value = null;
        user.value = null;
        localStorage.removeItem('access_token');
        localStorage.removeItem('user');
        delete axios.defaults.headers.common['Authorization'];

        // Forzar recarga completa (importante para limpiar sesión)
        window.location.href = '/login';
    };

    const hasRole = (roleName) => {
        return user.value?.roles?.includes(roleName) || false;
    };

    return { login, logout, isAuthenticated, hasRole, user, token };
}
```

### 2. Interceptores de Axios

**[resources/js/app.js](../resources/js/app.js)**

```javascript
import { useAuth } from './composables/useAuth';
import axios from 'axios';

const { initializeAuth, logout } = useAuth();
initializeAuth();

// Interceptor de Request: Inyectar token
axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('access_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
});

// Interceptor de Response: Manejar 401
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            logout(); // Auto-logout si token expiró
        }
        return Promise.reject(error);
    }
);
```

### 3. Componente de Login

**[resources/js/Pages/Auth/Login.vue](../resources/js/Pages/Auth/Login.vue)**

```vue
<script setup>
import { useAuth } from '@/composables/useAuth';

const { login } = useAuth();
const processing = ref(false);

const submit = async () => {
    processing.value = true;
    try {
        await login({
            email: form.value.email,
            password: form.value.password,
        });

        // IMPORTANTE: Forzar recarga completa
        window.location.href = '/dashboard';
    } catch (error) {
        // Manejar errores (401, 403, 422)
        processing.value = false;
    }
};
</script>
```

✅ **Clave**: Usa `window.location.href` para forzar recarga (NO `router.visit()`)

### 4. Layout Autenticado

**[resources/js/Layouts/AuthenticatedLayout.vue](../resources/js/Layouts/AuthenticatedLayout.vue)**

```vue
<script setup>
import { useAuth } from '@/composables/useAuth';

const { user: authUser, logout, hasRole } = useAuth();

// Usuario desde token OAuth o Inertia props
const currentUser = computed(() => {
    return authUser.value || page.props.auth?.user;
});

const isRoot = computed(() => hasRole('root'));

const handleLogout = async () => {
    await logout();
};
</script>

<template>
    <div>
        <span>{{ currentUser?.name }}</span>
        <button @click="handleLogout">Log Out</button>
    </div>
</template>
```

---

## 🔐 Flujo de Autenticación

### Login Flow

```
1. Usuario ingresa credenciales en /login
   ↓
2. Frontend: POST /api/login (OAuth 2.0)
   ↓
3. Backend valida credenciales y roles
   ↓
4. Backend genera:
   - JWT Token (access_token)
   - Sesión Web (Auth::guard('web')->login())
   ↓
5. Frontend recibe:
   - Token → localStorage
   - User data → localStorage
   ↓
6. Frontend: window.location.href = '/dashboard'
   (Recarga completa para activar sesión)
   ↓
7. Usuario accede al dashboard con sesión activa
```

### Navegación en Dashboard

```
- Inertia.js usa SESIÓN WEB (cookies)
- No requiere pasar tokens en cada request de Inertia
- El guard 'web' valida automáticamente
```

### API Calls

```
- Axios inyecta automáticamente: Authorization: Bearer {token}
- El guard 'api' valida el JWT token
- Si token expiró (401) → logout automático
```

### Logout Flow

```
1. Usuario hace clic en "Log Out"
   ↓
2. Frontend: POST /api/logout
   ↓
3. Backend:
   - Revoca token OAuth (JWT)
   - Cierra sesión web (Auth::guard('web')->logout())
   - Invalida sesión ($request->session()->invalidate())
   ↓
4. Frontend:
   - Limpia localStorage
   - Elimina header de Axios
   - window.location.href = '/login'
   ↓
5. Usuario ve página de login
```

---

## 📊 Base de Datos

### Tablas de OAuth 2.0 (Passport)

```sql
-- Tokens de acceso
oauth_access_tokens
  - id (PK)
  - user_id (FK → users)
  - client_id
  - name
  - scopes
  - revoked (boolean)
  - created_at
  - updated_at
  - expires_at

-- Refresh tokens
oauth_refresh_tokens
  - id (PK)
  - access_token_id (FK → oauth_access_tokens)
  - revoked (boolean)
  - expires_at

-- Clientes OAuth
oauth_clients
  - id (PK)
  - user_id
  - name
  - secret
  - provider
  - redirect
  - personal_access_client (boolean)
  - password_client (boolean)
  - revoked (boolean)
  - created_at
  - updated_at

-- Códigos de autorización
oauth_auth_codes
oauth_device_codes
```

### Verificar Tokens

```sql
-- Ver tokens activos de un usuario
SELECT id, user_id, name, revoked, expires_at, created_at
FROM oauth_access_tokens
WHERE user_id = 1
ORDER BY created_at DESC;

-- Ver tokens NO revocados
SELECT *
FROM oauth_access_tokens
WHERE revoked = 0 AND expires_at > NOW();
```

---

## ✅ Checklist de Verificación

### Backend
- [x] Laravel Passport instalado y configurado
- [x] Migraciones ejecutadas (`oauth_*` tables)
- [x] User model usa `Laravel\Passport\HasApiTokens`
- [x] AppServiceProvider configura Passport
- [x] Guard API usa driver `passport`
- [x] AuthController implementa login/logout híbrido
- [x] Rutas API usan middleware `auth:api` y `web`
- [x] Middleware CheckRole implementado
- [x] NO hay referencias a Sanctum en código PHP

### Frontend
- [x] Composable useAuth creado
- [x] Axios interceptors configurados
- [x] Login.vue usa OAuth 2.0 API
- [x] Layout usa useAuth para logout
- [x] window.location.href usado (no router.visit)
- [x] localStorage maneja tokens

### Seguridad
- [x] Tokens JWT firmados digitalmente
- [x] Expiración configurada (15 días access, 30 días refresh)
- [x] Sesiones invalidadas en logout
- [x] CSRF tokens regenerados
- [x] Roles verificados en cada request
- [x] Guard 'web' especificado explícitamente

### Funcionalidad
- [x] Login funciona correctamente
- [x] Logout cierra sesión completamente
- [x] Dashboard accesible después de login
- [x] No se puede acceder al dashboard sin login
- [x] Perfil editable
- [x] Roles funcionan (root, administrador, trabajador)

---

## 📚 Documentación Relacionada

1. **[OAUTH2_GUIA.md](OAUTH2_GUIA.md)** - Guía completa de OAuth 2.0
2. **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Documentación de endpoints
3. **[FRONTEND_OAUTH_MIGRACION.md](FRONTEND_OAUTH_MIGRACION.md)** - Migración del frontend
4. **[PRUEBAS_LOGOUT_Y_PERFIL.md](PRUEBAS_LOGOUT_Y_PERFIL.md)** - Guía de pruebas

---

## 🚀 Comandos Útiles

### Desarrollo
```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas API
php artisan route:list --path=api

# Generar nuevas claves Passport (si es necesario)
php artisan passport:keys
```

### Debugging
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Tinker para probar autenticación
php artisan tinker
>>> $user = User::find(1);
>>> $token = $user->createToken('Test')->accessToken;
```

### Base de Datos
```sql
-- Ver tokens de un usuario
SELECT * FROM oauth_access_tokens WHERE user_id = 1;

-- Revocar todos los tokens de un usuario
UPDATE oauth_access_tokens SET revoked = 1 WHERE user_id = 1;

-- Ver sesiones activas
SELECT * FROM sessions WHERE user_id IS NOT NULL;
```

---

## 🔧 Troubleshooting

### Problema: "Session store not set on request"
**Solución**: Agregar middleware `web` a la ruta:
```php
Route::middleware(['web'])->post('/api/login', ...);
```

### Problema: Logout no cierra la sesión
**Solución**: Especificar guard explícitamente:
```php
Auth::guard('web')->logout();
$request->session()->invalidate();
```

### Problema: Login funciona pero no llega al dashboard
**Solución**: Usar `window.location.href` en lugar de `router.visit()`:
```javascript
window.location.href = '/dashboard';
```

### Problema: Token expirado no redirige a login
**Solución**: Verificar interceptor de Axios:
```javascript
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            logout();
        }
        return Promise.reject(error);
    }
);
```

---

## 📝 Notas Importantes

1. **Autenticación Híbrida**: El sistema usa OAuth 2.0 para API + Sesiones para Inertia
2. **Guards Explícitos**: Siempre especificar `Auth::guard('web')` o `Auth::guard('api')`
3. **Recargas Completas**: Usar `window.location.href` para login/logout (no router.visit)
4. **Middleware Web**: Las rutas OAuth necesitan middleware `web` para sesiones
5. **Roles en Base de Datos**: root, administrador, trabajador, cliente

---

**Última actualización**: Diciembre 2025
**Versión**: 1.0.0
**Estado**: ✅ Producción
