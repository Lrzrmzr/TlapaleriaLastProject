# Guía de Tokens de Autenticación - API

## ¿Qué endpoints están disponibles?

### Endpoint Público (Sin autenticación)
```bash
GET /api/products
```
Este endpoint NO requiere autenticación y devuelve la lista de productos con información básica.

**Ejemplo:**
```bash
curl -X GET http://127.0.0.1:8000/api/products -H "Accept: application/json"
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Martillo",
      "description": "Martillo de acero",
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

### Endpoints Protegidos (Requieren autenticación)
Todos los endpoints bajo `/api/admin/*` requieren:
- Header: `Authorization: Bearer {token}`
- Rol: `empleado` o `administrador`

## Generar Token de Autenticación

### Opción 1: Usando el endpoint de login (RECOMENDADO)

La forma más sencilla es usar el endpoint `/api/login`:

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"tu@email.com","password":"tu_contraseña"}'
```

**Respuesta exitosa:**
```json
{
  "message": "Autenticación exitosa",
  "token": "1|abcdef123456789...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "tu@email.com",
    "roles": ["empleado"]
  }
}
```

Copia el valor del campo `token` y úsalo en tus peticiones.

### Opción 2: Desde la consola de Laravel (Tinker)

```bash
php artisan tinker
```

Luego ejecuta:
```php
// Obtener un usuario por email
$user = \App\Models\User::where('email', 'tu@email.com')->first();

// Verificar que tenga el rol correcto
$user->roles; // Debe tener rol "empleado" o "administrador"

// Generar token
$token = $user->createToken('api-token')->plainTextToken;

// Copiar este token
echo $token;
```

### Opción 3: Ejemplo con JavaScript/Fetch

```javascript
async function login(email, password) {
    const response = await fetch('http://127.0.0.1:8000/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email, password })
    });

    const data = await response.json();

    if (response.ok) {
        // Guardar el token para usarlo después
        localStorage.setItem('api_token', data.token);
        console.log('Login exitoso:', data);
        return data;
    } else {
        console.error('Error de login:', data.message);
        throw new Error(data.message);
    }
}

// Uso
login('tu@email.com', 'tu_contraseña')
    .then(data => console.log('Token:', data.token))
    .catch(error => console.error(error));
```

## Usar el Token en las Peticiones

### Con cURL
```bash
curl -X GET http://127.0.0.1:8000/api/admin/products \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

### Con JavaScript/Fetch
```javascript
fetch('http://127.0.0.1:8000/api/admin/products', {
  headers: {
    'Accept': 'application/json',
    'Authorization': 'Bearer TU_TOKEN_AQUI'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

### Con Axios
```javascript
axios.get('http://127.0.0.1:8000/api/admin/products', {
  headers: {
    'Authorization': 'Bearer TU_TOKEN_AQUI'
  }
})
.then(response => console.log(response.data));
```

### Con Postman
1. Selecciona la pestaña "Authorization"
2. Type: "Bearer Token"
3. Token: Pega tu token aquí

## Respuestas de Error

### Sin token o token inválido (401)
```json
{
  "message": "No autenticado. Por favor, proporcione un token de acceso válido."
}
```

### Sin rol adecuado (403)
```json
{
  "message": "No tienes permisos para acceder a este recurso"
}
```

## Revocar Tokens

Para revocar todos los tokens de un usuario:
```php
$user->tokens()->delete();
```

Para revocar un token específico:
```php
$user->tokens()->where('id', $tokenId)->delete();
```

## Verificar Token Actual

Puedes usar el endpoint `/api/user` para verificar qué usuario está autenticado con el token:

```bash
curl -X GET http://127.0.0.1:8000/api/user \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

## Notas de Seguridad

1. **NUNCA** compartas tus tokens
2. Los tokens tienen acceso completo a la cuenta del usuario
3. En producción, usa HTTPS siempre
4. Considera implementar expiración de tokens
5. Revoca tokens cuando ya no se necesiten
6. Guarda los tokens de forma segura (variables de entorno, almacenamiento seguro)
