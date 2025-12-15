# Resumen Rápido - API Ferretería TOTORO

## 🔓 Endpoints Públicos (Sin autenticación)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/products` | Lista de productos con información básica |
| `POST` | `/api/login` | Autenticación y generación de token |

## 🔐 Endpoints de Autenticación (Requieren token)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/me` | Información del usuario autenticado |
| `GET` | `/api/user` | Información del usuario (alternativo) |
| `POST` | `/api/logout` | Cierra sesión actual (revoca token) |
| `POST` | `/api/logout-all` | Cierra todas las sesiones |

## 🛡️ Endpoints Administrativos (Requieren token + rol)

**Roles permitidos:** `empleado` o `administrador`

### Productos
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/admin/products` | Listar productos |
| `POST` | `/api/admin/products` | Crear producto |
| `GET` | `/api/admin/products/{id}` | Obtener producto |
| `PUT` | `/api/admin/products/{id}` | Actualizar producto |
| `DELETE` | `/api/admin/products/{id}` | Eliminar producto |

### Proveedores
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/admin/suppliers` | Listar proveedores |
| `POST` | `/api/admin/suppliers` | Crear proveedor |
| `GET` | `/api/admin/suppliers/{id}` | Obtener proveedor |
| `PUT` | `/api/admin/suppliers/{id}` | Actualizar proveedor |
| `DELETE` | `/api/admin/suppliers/{id}` | Eliminar proveedor |

### Inventario
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/admin/inventory` | Listar inventario |
| `POST` | `/api/admin/inventory` | Crear entrada |
| `GET` | `/api/admin/inventory/{id}` | Obtener entrada |
| `PUT` | `/api/admin/inventory/{id}` | Actualizar entrada |
| `DELETE` | `/api/admin/inventory/{id}` | Eliminar entrada |

### Ventas
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/admin/ventas` | Listar ventas |
| `POST` | `/api/admin/ventas` | Registrar venta |
| `GET` | `/api/admin/ventas/{id}` | Obtener venta |
| `DELETE` | `/api/admin/ventas/{id}` | Eliminar venta |

### Faltantes
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/admin/faltantes` | Listar faltantes |
| `POST` | `/api/admin/faltantes` | Registrar faltante |
| `GET` | `/api/admin/faltantes/{id}` | Obtener faltante |
| `PUT` | `/api/admin/faltantes/{id}` | Actualizar faltante |
| `DELETE` | `/api/admin/faltantes/{id}` | Eliminar faltante |

---

## 🚀 Inicio Rápido

### 1. Obtener Token
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"tu@email.com","password":"tu_password"}'
```

### 2. Usar Token
```bash
curl -X GET http://127.0.0.1:8000/api/admin/products \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

---

## 📚 Documentación Completa

- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Documentación completa con ejemplos
- **[GUIA_TOKENS.md](GUIA_TOKENS.md)** - Guía de autenticación y manejo de tokens

---

## ⚠️ Códigos de Error Comunes

| Código | Mensaje | Significado |
|--------|---------|-------------|
| `401` | No autenticado | Falta token o es inválido |
| `403` | No tienes permisos | Usuario sin rol adecuado |
| `404` | Not Found | Recurso no existe |
| `422` | Validation Error | Datos inválidos en la petición |
