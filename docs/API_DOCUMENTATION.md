# Documentación de API - Ferretería TOTORO

## Índice
- [Autenticación](#autenticación)
- [Endpoints Públicos](#endpoints-públicos)
- [Endpoints de Autenticación](#endpoints-de-autenticación)
- [Endpoints Protegidos](#endpoints-protegidos-requieren-autenticación)
- [Respuestas de Error](#respuestas-de-error)

---

## Autenticación

La API utiliza **Laravel Passport (OAuth 2.0)** para autenticación y autorización.

### ¿Qué es OAuth 2.0?

OAuth 2.0 es el protocolo estándar de la industria para autenticación de APIs. Ofrece:

- ✅ **Tokens JWT firmados** - No se pueden falsificar
- ✅ **Expiración automática** - Tokens expiran en 15 días
- ✅ **Refresh Tokens** - Renovar sin pedir credenciales
- ✅ **Mayor seguridad** - Estándar probado en millones de aplicaciones

**📖 Para una guía completa de OAuth 2.0, consulta [OAUTH2_GUIA.md](OAUTH2_GUIA.md)**

### Roles Requeridos
Los siguientes roles tienen acceso a los endpoints administrativos:
- **Root**: Acceso total al sistema (super administrador)
- **Administrador**: Puede acceder a todos los endpoints administrativos
- **Trabajador**: Puede acceder a los endpoints administrativos
- **Cliente**: Solo puede consultar endpoints públicos (sin autenticación)

### Formato de Autenticación
Todas las peticiones a endpoints protegidos deben incluir el header:
```
Authorization: Bearer {tu_access_token_aqui}
```

**Nota:** El token ahora es un JWT (JSON Web Token) firmado digitalmente.

---

## Endpoints Públicos

Estos endpoints NO requieren autenticación.

### GET /api/products
Obtiene la lista de productos disponibles.

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Martillo de acero",
      "description": "Martillo profesional de acero templado",
      "category": "Herramientas",
      "suppliers": [
        {
          "id": 1,
          "name": "Proveedor ABC"
        }
      ]
    }
  ]
}
```

---

## Endpoints de Autenticación

### POST /api/login
Autentica un usuario y genera un token de acceso.

**Body (JSON):**
```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña"
}
```

**Respuesta exitosa (200):**
```json
{
  "message": "Autenticación exitosa",
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
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

**Cambios respecto a versión anterior:**
- `token` → `access_token` (ahora es un JWT)
- Nuevo campo `expires_in` (segundos hasta expiración = 15 días)

**Respuesta error - Credenciales incorrectas (401):**
```json
{
  "message": "Las credenciales proporcionadas son incorrectas."
}
```

**Respuesta error - Sin permisos (403):**
```json
{
  "message": "No tienes permisos para acceder a la API. Solo usuarios autorizados pueden autenticarse."
}
```

**Nota:** Solo usuarios con rol `root`, `administrador` o `trabajador` pueden autenticarse en la API.

### POST /api/logout
Cierra la sesión actual (revoca el token actual).

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "message": "Sesión cerrada exitosamente"
}
```

### POST /api/logout-all
Cierra todas las sesiones del usuario (revoca todos los tokens).

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "message": "Se han cerrado todas las sesiones exitosamente"
}
```

### GET /api/me
Obtiene información del usuario autenticado y del token actual.

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

**Nuevo:** Ahora incluye información del token (expiración, scopes, estado)

---

## Endpoints Protegidos (Requieren Autenticación)

Todos los endpoints administrativos requieren:
- Header: `Authorization: Bearer {token}`
- Rol: `root`, `administrador` o `trabajador`

### Información del Usuario
**GET /api/user**
- Retorna información del usuario autenticado

### Productos Administrativos
**Prefix:** `/api/admin/products`

- **GET /api/admin/products** - Listar todos los productos
- **POST /api/admin/products** - Crear nuevo producto
- **GET /api/admin/products/{id}** - Obtener producto específico
- **PUT /api/admin/products/{id}** - Actualizar producto
- **DELETE /api/admin/products/{id}** - Eliminar producto

### Proveedores
**Prefix:** `/api/admin/suppliers`

- **GET /api/admin/suppliers** - Listar todos los proveedores
- **POST /api/admin/suppliers** - Crear nuevo proveedor
- **GET /api/admin/suppliers/{id}** - Obtener proveedor específico
- **PUT /api/admin/suppliers/{id}** - Actualizar proveedor
- **DELETE /api/admin/suppliers/{id}** - Eliminar proveedor

### Inventario
**Prefix:** `/api/admin/inventory`

- **GET /api/admin/inventory** - Listar inventario
- **POST /api/admin/inventory** - Agregar entrada de inventario
- **GET /api/admin/inventory/{id}** - Obtener entrada específica
- **PUT /api/admin/inventory/{id}** - Actualizar entrada
- **DELETE /api/admin/inventory/{id}** - Eliminar entrada

### Ventas
**Prefix:** `/api/admin/ventas`

- **GET /api/admin/ventas** - Listar ventas
- **POST /api/admin/ventas** - Registrar nueva venta
- **GET /api/admin/ventas/{id}** - Obtener venta específica
- **DELETE /api/admin/ventas/{id}** - Eliminar venta

### Faltantes
**Prefix:** `/api/admin/faltantes`

- **GET /api/admin/faltantes** - Listar faltantes
- **POST /api/admin/faltantes** - Registrar nuevo faltante
- **GET /api/admin/faltantes/{id}** - Obtener faltante específico
- **PUT /api/admin/faltantes/{id}** - Actualizar faltante
- **DELETE /api/admin/faltantes/{id}** - Eliminar faltante

## Respuestas de Error

### 401 Unauthorized
```json
{
  "message": "No autenticado"
}
```

### 403 Forbidden
```json
{
  "message": "No tienes permisos para acceder a este recurso"
}
```

## Generar Token de Autenticación

Para generar un token de autenticación para usuarios con roles de empleado o administrador:

```php
// En un controlador de login
$token = $user->createToken('api-token')->plainTextToken;
```

El token debe enviarse en cada petición protegida:
```
Authorization: Bearer {token}
```

## Notas Importantes

1. El endpoint `/api/products` es público y NO requiere autenticación
2. Todos los endpoints bajo `/api/admin/*` requieren autenticación Bearer Token
3. Los usuarios deben tener rol de `empleado` o `administrador` para acceder a endpoints administrativos
4. El endpoint público solo retorna información básica: nombre, descripción, categoría y proveedores
5. NO se incluye información sensible como precios, inventario o historial en el endpoint público
