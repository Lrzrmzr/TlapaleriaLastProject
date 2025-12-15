# Guía de OAuth 2.0 - Ferretería TOTORO

## ¿Qué es OAuth 2.0?

OAuth 2.0 es un **protocolo de autorización** estándar de la industria que permite a las aplicaciones obtener acceso limitado a cuentas de usuario de forma segura.

### Ventajas de OAuth 2.0 sobre tokens simples:

✅ **Tokens con expiración automática** - Mayor seguridad
✅ **Refresh Tokens** - Renovar sesión sin pedir credenciales
✅ **Firma digital (JWT)** - Los tokens no se pueden falsificar
✅ **Estándar de la industria** - Compatible con cualquier cliente OAuth 2.0
✅ **Scopes/Permisos** - Control granular de acceso
✅ **Revocación de tokens** - Invalidar sesiones específicas

---

## Flujo de Autenticación

### 1. Login (Obtener Access Token)

**Endpoint:** `POST /api/login`

**Body:**
```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña"
}
```

**Respuesta Exitosa:**
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
    "roles": ["empleado"]
  }
}
```

**Campos importantes:**
- `access_token`: Token JWT que usarás en cada petición
- `token_type`: Siempre "Bearer"
- `expires_in`: Tiempo en segundos hasta que expire (15 días = 1,296,000 segundos)

---

### 2. Usar el Access Token

Incluye el token en cada petición protegida:

```http
GET /api/admin/products
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Ejemplo con cURL:**
```bash
curl -X GET http://127.0.0.1:8000/api/admin/products \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

**Ejemplo con JavaScript:**
```javascript
const token = 'eyJ0eXAiOiJKV1QiLCJhbGc...';

fetch('http://127.0.0.1:8000/api/admin/products', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

---

### 3. Renovar el Token (Refresh Token)

**NOTA:** El refresh token automático de Passport está disponible vía el endpoint estándar `/oauth/token`.

Para obtener información sobre cómo renovar:

**Endpoint:** `GET /api/refresh-info`

Este endpoint te dará instrucciones de cómo usar el refresh token.

---

### 4. Logout (Revocar Token)

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {tu_token}
```

**Respuesta:**
```json
{
  "message": "Sesión cerrada exitosamente. El token ha sido revocado."
}
```

---

### 5. Logout de Todos los Dispositivos

**Endpoint:** `POST /api/logout-all`

**Headers:**
```
Authorization: Bearer {tu_token}
```

**Respuesta:**
```json
{
  "message": "Se han cerrado todas las sesiones exitosamente. Todos los tokens han sido revocados."
}
```

---

## Configuración de Expiración

Los tokens están configurados con los siguientes tiempos de expiración:

| Token Type | Duración | Descripción |
|------------|----------|-------------|
| **Access Token** | 15 días | Token principal para acceder a la API |
| **Refresh Token** | 30 días | Para renovar el access token |
| **Personal Access Token** | 6 meses | Para aplicaciones de confianza total |

---

## Información del Token

**Endpoint:** `GET /api/me`

Obtiene información del usuario y del token actual:

**Respuesta:**
```json
{
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@ejemplo.com",
    "roles": ["empleado"],
    "created_at": "2024-01-01T00:00:00.000000Z"
  },
  "token_info": {
    "scopes": [],
    "expires_at": "2024-01-16T00:00:00.000000Z",
    "revoked": false
  }
}
```

---

## ¿Qué es un JWT (JSON Web Token)?

Un JWT es un token **firmado digitalmente** con 3 partes:

```
eyJ0eXAiOiJKV1QiLCJhbGc...  ←  Header (algoritmo)
eyJzdWIiOiIxMjM0NTY...      ←  Payload (datos del usuario)
SflKxwRJSMeKKF2QT4...       ←  Signature (firma digital)
```

### Ventajas del JWT:

1. **No se puede falsificar** - Tiene firma digital
2. **Stateless** - No necesita consultar BD en cada petición
3. **Contiene información** - Lleva datos del usuario
4. **Expiración automática** - El servidor verifica si expiró

---

## Códigos de Error

| Código | Descripción | Solución |
|--------|-------------|----------|
| **401** | Token inválido o expirado | Hacer login nuevamente |
| **403** | Sin permisos (rol incorrecto) | Usar cuenta con rol adecuado |
| **422** | Datos de login incorrectos | Verificar email/password |

---

## Seguridad

### ✅ Buenas Prácticas

1. **Guarda el token de forma segura**
   - En app móvil: Keychain (iOS) o Keystore (Android)
   - En web: No en localStorage, mejor en memoria o httpOnly cookies

2. **Usa HTTPS en producción**
   - Los tokens nunca deben viajar por HTTP plano

3. **Maneja la expiración**
   - Implementa renovación automática con refresh token
   - Redirige al login cuando expire

4. **Revoca tokens cuando sea necesario**
   - Al cambiar contraseña
   - Al cerrar sesión
   - Si detectas actividad sospechosa

### ❌ Malas Prácticas

- ❌ No compartas tokens entre usuarios
- ❌ No guardes tokens en código fuente
- ❌ No envíes tokens por URL (query params)
- ❌ No uses el mismo token en múltiples apps

---

## Diferencias: Sanctum vs Passport (OAuth 2.0)

| Característica | Sanctum (Anterior) | Passport (Actual) |
|----------------|-------------------|-------------------|
| Tipo de token | Hash simple en BD | JWT firmado |
| Expiración | Manual | Automática |
| Refresh tokens | ❌ No | ✅ Sí |
| Estándar | Propio de Laravel | OAuth 2.0 |
| Seguridad | Media | Alta |
| Apps de terceros | ❌ No | ✅ Sí |
| Complejidad | Baja | Media |

---

## Endpoints Adicionales de OAuth 2.0

Passport también expone endpoints estándar de OAuth 2.0:

- `POST /oauth/token` - Obtener/renovar tokens
- `GET /oauth/tokens` - Listar tokens del usuario
- `DELETE /oauth/tokens/{id}` - Revocar token específico
- `GET /oauth/clients` - Listar clientes OAuth
- `POST /oauth/clients` - Crear nuevo cliente

Para más información sobre estos endpoints, consulta la [documentación oficial de Passport](https://laravel.com/docs/11.x/passport).

---

## Migración desde Sanctum

Si estabas usando Sanctum, los cambios principales son:

1. **Header de autenticación** - Sigue siendo `Authorization: Bearer {token}`
2. **Respuesta de login** - Ahora incluye `access_token` en lugar de `token`
3. **Expiración** - Los tokens ahora expiran automáticamente en 15 días
4. **Renovación** - Usa refresh tokens en lugar de crear nuevos tokens

---

## Soporte

Para más información:
- [Documentación completa de API](API_DOCUMENTATION.md)
- [Resumen de endpoints](API_RESUMEN.md)
- [Documentación oficial de Laravel Passport](https://laravel.com/docs/11.x/passport)
