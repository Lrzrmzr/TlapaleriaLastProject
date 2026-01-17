# API para Aplicación Móvil - Ferretería TOTORO

## URL Base
```
http://tu-dominio.com/api
```

## Autenticación

Todos los endpoints (excepto `/login`) requieren autenticación mediante OAuth 2.0 (Laravel Passport).

### Login
**POST** `/login`

**Body:**
```json
{
  "email": "usuario@example.com",
  "password": "password123"
}
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "role": "administrador",
      "branch_id": 1,
      "branch": {
        "id": 1,
        "name": "Sucursal Centro",
        "code": "SC001"
      }
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

### Headers para Requests Autenticados
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Información del Usuario Actual
**GET** `/me`

### Logout
**POST** `/logout`

---

## Productos

### Listar Productos
**GET** `/mobile/products`

**Query Parameters:**
- `search` (string): Buscar por nombre, descripción o código de barras
- `supplier_id` (int): Filtrar por proveedor
- `category_id` (int): Filtrar por categoría
- `per_page` (int, default: 50): Resultados por página

**Ejemplo:**
```
GET /mobile/products?search=martillo&per_page=20
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Martillo de Uña 16oz",
      "description": "Martillo profesional con mango de fibra de vidrio",
      "code": "MART001",
      "barcode": "7501234567890",
      "costo": 150.00,
      "precio": 250.00,
      "supplier": {
        "id": 1,
        "name": "Herramientas SA"
      },
      "categories": [
        {
          "id": 2,
          "name": "Herramientas Manuales"
        }
      ]
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "last_page": 8
  }
}
```

### Buscar Producto por Código de Barras
**GET** `/mobile/products/barcode/{barcode}`

**Ejemplo:**
```
GET /mobile/products/barcode/7501234567890
```

### Ver Producto Específico
**GET** `/mobile/products/{id}`

### Crear Producto
**POST** `/mobile/products`

**Body:**
```json
{
  "name": "Martillo de Uña 16oz",
  "description": "Martillo profesional",
  "code": "MART001",
  "barcode": "7501234567890",
  "costo": 150.00,
  "precio": 250.00,
  "supplier_id": 1
}
```

### Actualizar Producto
**PUT** `/mobile/products/{id}`

### Eliminar Producto
**DELETE** `/mobile/products/{id}`

---

## Inventario

### Listar Inventario
**GET** `/mobile/inventory`

**Query Parameters:**
- `branch_id` (int): Filtrar por sucursal (si es root)
- `search` (string): Buscar por nombre o código del producto
- `stock_status` (string): `agotado`, `critico`, `bajo`
- `per_page` (int, default: 50)

**Nota:** Los usuarios no-root solo ven el inventario de su sucursal.

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "branch_id": 1,
      "stock": 25,
      "min_stock": 10,
      "max_stock": 100,
      "product": {
        "id": 1,
        "name": "Martillo de Uña 16oz",
        "code": "MART001",
        "barcode": "7501234567890"
      },
      "branch": {
        "id": 1,
        "name": "Sucursal Centro"
      }
    }
  ],
  "pagination": {...}
}
```

### Ver Inventario Específico
**GET** `/mobile/inventory/{id}`

### Crear Inventario
**POST** `/mobile/inventory`

**Body:**
```json
{
  "product_id": 1,
  "branch_id": 1,
  "stock": 50,
  "min_stock": 10,
  "max_stock": 100
}
```

### Ajustar Stock
**POST** `/mobile/inventory/{id}/adjust`

**Body:**
```json
{
  "adjustment": -5,
  "reason": "Venta manual"
}
```

**Nota:** El `adjustment` puede ser positivo (entrada) o negativo (salida).

**Respuesta:**
```json
{
  "success": true,
  "message": "Stock ajustado exitosamente",
  "data": {
    "old_stock": 25,
    "adjustment": -5,
    "new_stock": 20,
    "product": "Martillo de Uña 16oz"
  }
}
```

### Productos sin Inventario
**GET** `/mobile/inventory/productos-sin-inventario?branch_id=1`

---

## Ventas

### Listar Ventas
**GET** `/mobile/ventas`

**Query Parameters:**
- `branch_id` (int): Filtrar por sucursal
- `date_from` (date): Fecha desde (YYYY-MM-DD)
- `date_to` (date): Fecha hasta (YYYY-MM-DD)
- `per_page` (int, default: 50)

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "branch_id": 1,
      "user_id": 1,
      "subtotal": 500.00,
      "total": 500.00,
      "utilidad": 150.00,
      "created_at": "2024-12-21T10:30:00.000000Z",
      "items": [
        {
          "id": 1,
          "product_id": 1,
          "quantity": 2,
          "precio_costo": 150.00,
          "precio_venta": 250.00,
          "subtotal": 500.00,
          "utilidad": 200.00,
          "product": {
            "id": 1,
            "name": "Martillo de Uña 16oz"
          }
        }
      ],
      "user": {
        "id": 1,
        "name": "Juan Pérez"
      },
      "branch": {
        "id": 1,
        "name": "Sucursal Centro"
      }
    }
  ],
  "pagination": {...},
  "stats": {
    "total_ventas": 15000.00,
    "total_utilidad": 4500.00
  }
}
```

### Ver Venta Específica
**GET** `/mobile/ventas/{id}`

### Crear Venta
**POST** `/mobile/ventas`

**Body:**
```json
{
  "branch_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "precio_venta": 250.00
    },
    {
      "product_id": 5,
      "quantity": 1,
      "precio_venta": 180.00
    }
  ]
}
```

**Nota:** El sistema calcula automáticamente:
- Subtotal y total
- Utilidad (basado en el costo del producto)
- Actualiza el stock del inventario

### Eliminar Venta
**DELETE** `/mobile/ventas/{id}`

**Nota:** Solo administradores y root. Restaura el stock al inventario.

---

## Gastos

### Listar Gastos
**GET** `/mobile/gastos`

**Query Parameters:**
- `date_from` (date): Fecha desde
- `date_to` (date): Fecha hasta
- `per_page` (int, default: 50)

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "descripcion": "Compra de material de oficina",
      "total": 350.00,
      "user_id": 1,
      "created_at": "2024-12-21T09:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "Juan Pérez"
      }
    }
  ],
  "pagination": {...},
  "stats": {
    "total": 5000.00
  }
}
```

### Ver Gasto Específico
**GET** `/mobile/gastos/{id}`

### Crear Gasto
**POST** `/mobile/gastos`

**Body:**
```json
{
  "descripcion": "Pago de luz",
  "total": 450.00
}
```

### Actualizar Gasto
**PUT** `/mobile/gastos/{id}`

### Eliminar Gasto
**DELETE** `/mobile/gastos/{id}`

**Nota:** Solo administradores y root.

---

## Faltantes

### Listar Faltantes
**GET** `/mobile/faltantes`

**Query Parameters:**
- `branch_id` (int): Filtrar por sucursal
- `status` (string): `pendiente`, `en_proceso`, `completado`, `cancelado`
- `product_id` (int): Filtrar por producto
- `date_from` (date): Fecha desde
- `date_to` (date): Fecha hasta
- `per_page` (int, default: 50)

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "branch_id": 1,
      "user_id": 1,
      "cantidad_solicitada": 20,
      "status": "pendiente",
      "urgente": true,
      "observaciones": "Se requiere para pedido grande",
      "created_at": "2024-12-21T08:00:00.000000Z",
      "product": {
        "id": 1,
        "name": "Martillo de Uña 16oz"
      },
      "user": {
        "id": 1,
        "name": "Juan Pérez"
      },
      "branch": {
        "id": 1,
        "name": "Sucursal Centro"
      }
    }
  ],
  "pagination": {...},
  "stats": {
    "total_pendientes": 15
  }
}
```

### Ver Faltante Específico
**GET** `/mobile/faltantes/{id}`

### Crear Faltante
**POST** `/mobile/faltantes`

**Body:**
```json
{
  "product_id": 1,
  "branch_id": 1,
  "cantidad_solicitada": 20,
  "urgente": true,
  "observaciones": "Se requiere para pedido grande"
}
```

### Actualizar Faltante
**PUT** `/mobile/faltantes/{id}`

**Body:**
```json
{
  "cantidad_solicitada": 25,
  "status": "en_proceso",
  "observaciones": "Actualizado a 25 unidades"
}
```

### Marcar como Completado
**POST** `/mobile/faltantes/{id}/complete`

### Eliminar Faltante
**DELETE** `/mobile/faltantes/{id}`

**Nota:** Solo administradores y root.

---

## Sucursales

### Listar Sucursales
**GET** `/mobile/branches`

**Query Parameters:**
- `active` (boolean): Filtrar por activas/inactivas
- `search` (string): Buscar por nombre o código

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Sucursal Centro",
      "code": "SC001",
      "address": "Calle Principal #123",
      "phone": "555-1234",
      "active": true
    }
  ]
}
```

### Ver Sucursal Específica
**GET** `/mobile/branches/{id}`

### Estadísticas de Sucursal
**GET** `/mobile/branches/{id}/stats`

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "branch": {
      "id": 1,
      "name": "Sucursal Centro",
      "code": "SC001"
    },
    "stats": {
      "total_inventarios": 450,
      "stock_bajo": 25,
      "agotados": 8,
      "ventas_hoy": 15000.00,
      "gastos_mes": 8500.00,
      "faltantes_pendientes": 12
    }
  }
}
```

---

## Códigos de Estado HTTP

- **200 OK**: Petición exitosa
- **201 Created**: Recurso creado exitosamente
- **400 Bad Request**: Error de validación o datos incorrectos
- **401 Unauthorized**: No autenticado o token inválido
- **403 Forbidden**: Sin permisos para realizar la acción
- **404 Not Found**: Recurso no encontrado
- **500 Internal Server Error**: Error del servidor

---

## Formato de Respuestas de Error

```json
{
  "success": false,
  "message": "Descripción del error"
}
```

**Errores de validación:**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "El campo email es obligatorio."
    ],
    "password": [
      "El campo password debe tener al menos 8 caracteres."
    ]
  }
}
```

---

## Notas Importantes

1. **Paginación**: La mayoría de los endpoints de listado soportan paginación con `per_page` y retornan metadata de paginación.

2. **Filtrado por Sucursal**: Los usuarios no-root solo pueden ver datos de su sucursal asignada.

3. **Permisos**: Algunas acciones (eliminar ventas, gastos, faltantes) requieren rol de administrador o root.

4. **Transacciones**: Las ventas actualizan automáticamente el inventario. Si una venta se elimina, el stock se restaura.

5. **Fechas**: Todas las fechas se devuelven en formato ISO 8601 (UTC).

6. **Stock Negativo**: No se permite que el stock sea negativo en ajustes o ventas.

---

## Próximos Pasos

Una vez que tengas los endpoints funcionando, puedes:

1. Configurar Capacitor para la app móvil
2. Implementar un servicio de API en la app móvil usando Axios o Fetch
3. Manejar el almacenamiento del token en el dispositivo
4. Implementar sincronización offline (opcional)
5. Agregar notificaciones push (opcional)
