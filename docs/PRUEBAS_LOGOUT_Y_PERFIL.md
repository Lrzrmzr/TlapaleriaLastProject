# Guía de Pruebas - Logout y Perfil

## Correcciones Aplicadas

### 1. Problema de Logout Resuelto

**Problema anterior:**
- El logout limpiaba el token OAuth y localStorage
- Pero NO invalidaba la sesión web de Laravel
- Por eso seguías autenticado después de hacer logout

**Solución aplicada:**
En [app/Http/Controllers/Api/AuthController.php](../app/Http/Controllers/Api/AuthController.php):

```php
public function logout(Request $request)
{
    // 1. Revocar token OAuth
    $request->user()->token()->revoke();

    // 2. Cerrar sesión web
    Auth::logout();

    // 3. NUEVO: Invalidar sesión completamente
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
        'message' => 'Sesión cerrada exitosamente.'
    ], 200);
}
```

**Qué hace cada línea:**
1. `token()->revoke()` - Marca el JWT como revocado en BD
2. `Auth::logout()` - Cierra la sesión del usuario
3. `session()->invalidate()` - Destruye la sesión actual
4. `session()->regenerateToken()` - Genera nuevo CSRF token (seguridad)

## Cómo Probar el Logout

### Prueba 1: Logout Básico

1. **Iniciar sesión:**
   - Ve a `http://127.0.0.1:8000/login`
   - Ingresa tus credenciales
   - Verifica que llegues al dashboard

2. **Verificar estado inicial:**
   - Abre las DevTools (F12)
   - Ve a la pestaña "Application" → "Local Storage"
   - Deberías ver:
     - `access_token`: (un JWT largo)
     - `user`: (objeto JSON con tu información)

3. **Hacer logout:**
   - Haz clic en tu nombre en la esquina superior derecha
   - Selecciona "Log Out"
   - Deberías ser redirigido a `/login`

4. **Verificar que cerró sesión:**
   - En DevTools → "Local Storage":
     - `access_token` debe haberse eliminado
     - `user` debe haberse eliminado
   - Intenta navegar manualmente a `http://127.0.0.1:8000/dashboard`
   - Deberías ser redirigido automáticamente a `/login`

### Prueba 2: Logout en Consola

Puedes verificar el logout en la consola del navegador:

```javascript
// 1. Verificar token actual
console.log('Token:', localStorage.getItem('access_token'));
console.log('User:', localStorage.getItem('user'));

// 2. Hacer logout programáticamente (si necesitas)
import { useAuth } from '@/composables/useAuth';
const { logout } = useAuth();
await logout();

// 3. Verificar que se limpió
console.log('Token después:', localStorage.getItem('access_token')); // null
```

### Prueba 3: Verificar Revocación del Token

1. **Antes de hacer logout:**
   - Copia el `access_token` de localStorage
   - Prueba hacer una petición a la API:

```bash
curl -X GET http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

2. **Después de hacer logout:**
   - Intenta la misma petición con el mismo token
   - Deberías recibir un error 401 (Unauthorized)

```bash
curl -X GET http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer TU_TOKEN_ANTERIOR"
# Respuesta: {"message": "No autenticado"}
```

## Cómo Probar el Perfil

### Prueba 1: Actualizar Información del Perfil

1. **Ir a perfil:**
   - Navega a `http://127.0.0.1:8000/profile`
   - Deberías ver 3 secciones:
     - Profile Information
     - Update Password
     - Delete Account

2. **Actualizar nombre/email:**
   - En "Profile Information", cambia tu nombre
   - Por ejemplo: "Juan Pérez" → "Juan Pérez Modificado"
   - Haz clic en "Save"
   - Deberías ver un mensaje de éxito

3. **Verificar el cambio:**
   - Recarga la página
   - El nuevo nombre debe aparecer
   - También verifica en la esquina superior derecha del dashboard

### Prueba 2: Cambiar Contraseña

1. **Ir a "Update Password":**
   - Ingresa tu contraseña actual
   - Ingresa una nueva contraseña
   - Confirma la nueva contraseña
   - Haz clic en "Save"

2. **Verificar el cambio:**
   - Haz logout
   - Intenta iniciar sesión con la contraseña ANTERIOR
   - Debería fallar con error "Credenciales incorrectas"
   - Inicia sesión con la contraseña NUEVA
   - Debería funcionar correctamente

### Prueba 3: Verificar Validaciones

El sistema de perfil incluye validaciones. Prueba:

1. **Email duplicado:**
   - Intenta cambiar tu email a uno que ya existe en el sistema
   - Deberías ver error: "The email has already been taken"

2. **Contraseña actual incorrecta:**
   - En "Update Password", ingresa una contraseña actual incorrecta
   - Deberías ver error: "The current password is incorrect"

3. **Contraseñas no coinciden:**
   - Nueva contraseña: "password123"
   - Confirmar contraseña: "password456"
   - Deberías ver error: "The password confirmation does not match"

## Pruebas de Integración OAuth + Sesión

### Escenario 1: Token Expira (en 15 días)

El token OAuth expira en 15 días. Cuando esto suceda:

1. El interceptor de Axios detectará el error 401
2. Se ejecutará `logout()` automáticamente
3. Serás redirigido a `/login`
4. Deberás iniciar sesión nuevamente

**Para simular esto:**
```sql
-- En tu base de datos, actualiza la expiración del token
UPDATE oauth_access_tokens
SET expires_at = NOW() - INTERVAL 1 DAY
WHERE user_id = TU_USER_ID
ORDER BY created_at DESC
LIMIT 1;
```

Luego intenta navegar en el dashboard. Deberías ser deslogueado automáticamente.

### Escenario 2: Múltiples Pestañas

1. Abre el dashboard en 2 pestañas diferentes
2. En la pestaña 1, haz logout
3. En la pestaña 2, intenta navegar a otra página
4. Ambas pestañas deberían redirigirte a `/login`

**¿Por qué?**
- El logout invalida la sesión en el servidor
- Ambas pestañas comparten la misma sesión
- Al invalidar la sesión, afecta a todas las pestañas

### Escenario 3: Logout desde API (Postman/cURL)

Puedes hacer logout desde herramientas externas:

```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

Esto debería:
1. Revocar el token
2. Invalidar la sesión web
3. Cualquier página del dashboard abierta dejará de funcionar

## Errores Comunes y Soluciones

### Error: "CSRF token mismatch"

**Causa:** La sesión fue invalidada pero el frontend tiene un CSRF token viejo.

**Solución:**
- Esto es normal después de logout
- El sistema automáticamente te redirige a login
- Al hacer login de nuevo, se genera un nuevo CSRF token

### Error: "Unauthenticated" después de logout

**Causa:** Intentas acceder a una página protegida sin autenticación.

**Solución:**
- Esto es el comportamiento esperado
- Inicia sesión nuevamente

### Error: localStorage sigue teniendo el token después de logout

**Causa:** El navegador no actualizó localStorage.

**Solución:**
- Verifica en la consola: `localStorage.clear()` y recarga
- Si el problema persiste, revisa el código del composable useAuth

### El perfil no se actualiza

**Causa:** Posible error de validación o permisos.

**Solución:**
1. Abre DevTools → Network
2. Busca la petición PATCH a `/profile`
3. Verifica la respuesta del servidor
4. Revisa los errores de validación

## Verificación del Sistema Completo

### Checklist Final

- [ ] Login funciona con credenciales válidas
- [ ] Login falla con credenciales inválidas
- [ ] Dashboard carga correctamente después del login
- [ ] Token se guarda en localStorage
- [ ] User data se guarda en localStorage
- [ ] Logout limpia localStorage
- [ ] Logout invalida sesión web
- [ ] Después de logout, no puedes acceder al dashboard
- [ ] Perfil carga correctamente
- [ ] Puedes actualizar tu nombre
- [ ] Puedes actualizar tu email
- [ ] Puedes cambiar tu contraseña
- [ ] Las validaciones funcionan correctamente
- [ ] El interceptor de Axios detecta 401 y hace logout automático

## Logs para Debugging

Si algo no funciona, verifica:

### 1. Laravel Logs
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log
```

### 2. Browser Console
```javascript
// En DevTools Console
console.log('Token:', localStorage.getItem('access_token'));
console.log('User:', JSON.parse(localStorage.getItem('user')));
```

### 3. Network Tab
- Abre DevTools → Network
- Filtra por "XHR"
- Verifica las peticiones a `/api/logout` y `/profile`
- Revisa los headers de respuesta

### 4. Base de Datos
```sql
-- Ver tokens activos
SELECT user_id, expires_at, revoked, created_at
FROM oauth_access_tokens
WHERE user_id = TU_USER_ID
ORDER BY created_at DESC;

-- Ver sesiones activas
SELECT * FROM sessions
WHERE user_id = TU_USER_ID;
```

---

**Fecha de actualización:** Diciembre 2025
