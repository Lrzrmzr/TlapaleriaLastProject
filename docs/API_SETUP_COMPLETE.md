# Configuración de API Completada ✓

## Resumen

Se ha completado la configuración de la API REST para la aplicación móvil de Ferretería TOTORO. A continuación se detallan los componentes creados y configurados.

---

## 📁 Controladores API Creados

Todos los controladores están en `app/Http/Controllers/Api/`:

### 1. AuthController.php
**Funcionalidad**: Autenticación de usuarios mediante OAuth 2.0 (Laravel Passport)
- ✅ `POST /api/login` - Iniciar sesión y obtener token
- ✅ `GET /api/me` - Obtener información del usuario autenticado
- ✅ `POST /api/logout` - Cerrar sesión y revocar token

### 2. ProductApiController.php
**Funcionalidad**: Gestión completa de productos
- ✅ `GET /api/mobile/products` - Listar productos con filtros
- ✅ `GET /api/mobile/products/{id}` - Ver producto específico
- ✅ `POST /api/mobile/products` - Crear producto
- ✅ `PUT /api/mobile/products/{id}` - Actualizar producto
- ✅ `DELETE /api/mobile/products/{id}` - Eliminar producto
- ✅ `GET /api/mobile/products/barcode/{barcode}` - Buscar por código de barras

**Filtros disponibles**:
- `search` - Buscar por nombre, descripción o código de barras
- `supplier_id` - Filtrar por proveedor
- `category_id` - Filtrar por categoría
- `per_page` - Paginación

### 3. InventarioApiController.php
**Funcionalidad**: Gestión de inventario por sucursal
- ✅ `GET /api/mobile/inventory` - Listar inventario
- ✅ `GET /api/mobile/inventory/{id}` - Ver inventario específico
- ✅ `POST /api/mobile/inventory` - Crear inventario
- ✅ `POST /api/mobile/inventory/{id}/adjust` - Ajustar stock
- ✅ `GET /api/mobile/inventory/productos-sin-inventario` - Productos sin inventario

**Filtros disponibles**:
- `branch_id` - Filtrar por sucursal (solo root)
- `search` - Buscar por nombre/código/barcode del producto
- `stock_status` - `agotado`, `critico`, `bajo`
- `per_page` - Paginación

**Nota**: Usuarios no-root solo ven inventario de su sucursal.

### 4. GastoApiController.php
**Funcionalidad**: Gestión de gastos
- ✅ `GET /api/mobile/gastos` - Listar gastos
- ✅ `GET /api/mobile/gastos/{id}` - Ver gasto específico
- ✅ `POST /api/mobile/gastos` - Crear gasto
- ✅ `PUT /api/mobile/gastos/{id}` - Actualizar gasto
- ✅ `DELETE /api/mobile/gastos/{id}` - Eliminar gasto (solo admin/root)

**Filtros disponibles**:
- `date_from` - Fecha desde
- `date_to` - Fecha hasta
- `per_page` - Paginación

**Incluye estadísticas**: Total de gastos en la respuesta

### 5. VentaApiController.php
**Funcionalidad**: Gestión de ventas con control de inventario
- ✅ `GET /api/mobile/ventas` - Listar ventas
- ✅ `GET /api/mobile/ventas/{id}` - Ver venta específica
- ✅ `POST /api/mobile/ventas` - Crear venta
- ✅ `DELETE /api/mobile/ventas/{id}` - Eliminar venta (solo admin/root)

**Filtros disponibles**:
- `branch_id` - Filtrar por sucursal
- `date_from` - Fecha desde
- `date_to` - Fecha hasta
- `per_page` - Paginación

**Características especiales**:
- Calcula automáticamente subtotal, total y utilidad
- Actualiza el stock del inventario al crear venta
- Restaura el stock al eliminar venta
- Valida stock disponible antes de crear venta
- Transacciones de base de datos para integridad

**Incluye estadísticas**: Total de ventas y utilidad total

### 6. FaltanteApiController.php
**Funcionalidad**: Gestión de productos faltantes
- ✅ `GET /api/mobile/faltantes` - Listar faltantes
- ✅ `GET /api/mobile/faltantes/{id}` - Ver faltante específico
- ✅ `POST /api/mobile/faltantes` - Crear faltante
- ✅ `PUT /api/mobile/faltantes/{id}` - Actualizar faltante
- ✅ `POST /api/mobile/faltantes/{id}/complete` - Marcar como completado
- ✅ `DELETE /api/mobile/faltantes/{id}` - Eliminar faltante (solo admin/root)

**Filtros disponibles**:
- `branch_id` - Filtrar por sucursal
- `confirmado` - true/false para filtrar por estado
- `search` - Buscar en descripción o pedido
- `date_from` - Fecha desde
- `date_to` - Fecha hasta
- `per_page` - Paginación

**Incluye estadísticas**: Total de faltantes pendientes

### 7. BranchApiController.php
**Funcionalidad**: Información de sucursales y estadísticas
- ✅ `GET /api/mobile/branches` - Listar sucursales
- ✅ `GET /api/mobile/branches/{id}` - Ver sucursal específica
- ✅ `GET /api/mobile/branches/{id}/stats` - Estadísticas de sucursal

**Filtros disponibles**:
- `active` - Filtrar por activas/inactivas
- `search` - Buscar por nombre o código

**Estadísticas incluidas**:
- Total de inventarios
- Productos con stock bajo
- Productos agotados
- Ventas del día
- Gastos del mes
- Faltantes pendientes

---

## 📦 Modelos Creados/Actualizados

### Nuevos Modelos Creados:

1. **Venta.php**
   - Relaciones con: Branch, User, VentaItem
   - Campos: branch_id, user_id, subtotal, total, utilidad

2. **VentaItem.php**
   - Relaciones con: Venta, Product
   - Campos: venta_id, product_id, quantity, precio_costo, precio_venta, subtotal, utilidad

### Modelos Actualizados:

3. **Branch.php**
   - Agregadas relaciones: `ventas()` y `inventories()`
   - Para compatibilidad con los controladores API

---

## 🛣️ Rutas Configuradas

Archivo: `routes/api.php`

### Rutas Públicas:
```php
POST /api/login
```

### Rutas Protegidas (requieren autenticación):
```php
// Autenticación
GET  /api/me
POST /api/logout

// Productos
GET    /api/mobile/products
GET    /api/mobile/products/{id}
POST   /api/mobile/products
PUT    /api/mobile/products/{id}
DELETE /api/mobile/products/{id}
GET    /api/mobile/products/barcode/{barcode}

// Inventario
GET  /api/mobile/inventory
GET  /api/mobile/inventory/{id}
POST /api/mobile/inventory
POST /api/mobile/inventory/{id}/adjust
GET  /api/mobile/inventory/productos-sin-inventario

// Gastos
GET    /api/mobile/gastos
GET    /api/mobile/gastos/{id}
POST   /api/mobile/gastos
PUT    /api/mobile/gastos/{id}
DELETE /api/mobile/gastos/{id}

// Ventas
GET    /api/mobile/ventas
GET    /api/mobile/ventas/{id}
POST   /api/mobile/ventas
DELETE /api/mobile/ventas/{id}

// Faltantes
GET    /api/mobile/faltantes
GET    /api/mobile/faltantes/{id}
POST   /api/mobile/faltantes
PUT    /api/mobile/faltantes/{id}
POST   /api/mobile/faltantes/{id}/complete
DELETE /api/mobile/faltantes/{id}

// Sucursales
GET /api/mobile/branches
GET /api/mobile/branches/{id}
GET /api/mobile/branches/{id}/stats
```

---

## 🔐 Autenticación

### Sistema Utilizado: Laravel Passport (OAuth 2.0)

El proyecto ya tenía Passport instalado, por lo que se utilizó en lugar de Sanctum.

### Flujo de Autenticación:

1. **Login**:
   ```http
   POST /api/login
   Content-Type: application/json

   {
     "email": "usuario@example.com",
     "password": "password123"
   }
   ```

2. **Respuesta**:
   ```json
   {
     "success": true,
     "message": "Login exitoso",
     "data": {
       "user": {...},
       "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     }
   }
   ```

3. **Uso del Token**:
   ```http
   GET /api/mobile/products
   Authorization: Bearer {token}
   Content-Type: application/json
   Accept: application/json
   ```

---

## 📚 Documentación

### Archivos de Documentación Creados:

1. **API_MOBILE.md**
   - Documentación completa de todos los endpoints
   - Ejemplos de requests y responses
   - Códigos de estado HTTP
   - Formato de errores
   - Guía de uso

2. **API_SETUP_COMPLETE.md** (este archivo)
   - Resumen de la configuración
   - Lista de componentes creados
   - Próximos pasos

---

## ✅ Características Implementadas

### Seguridad:
- ✅ Autenticación OAuth 2.0 con Laravel Passport
- ✅ Validación de datos en todos los endpoints
- ✅ Control de permisos por roles (admin/root)
- ✅ Filtrado automático por sucursal para usuarios no-root

### Funcionalidad:
- ✅ Paginación en todos los listados
- ✅ Filtros avanzados en cada módulo
- ✅ Búsqueda por múltiples criterios
- ✅ Transacciones de base de datos para operaciones críticas
- ✅ Cálculos automáticos (totales, utilidades, etc.)
- ✅ Actualización automática de inventario en ventas
- ✅ Validaciones de stock antes de ventas
- ✅ Estadísticas en tiempo real

### Formato de Respuestas:
- ✅ Estructura consistente en todas las respuestas
- ✅ Metadata de paginación
- ✅ Estadísticas incluidas donde aplica
- ✅ Mensajes de error descriptivos
- ✅ Códigos de estado HTTP apropiados

---

## 🧪 Testing de la API

### Herramientas Recomendadas:
- Postman
- Insomnia
- Thunder Client (VS Code)
- curl

### Pasos para Probar:

1. **Probar Login**:
   ```bash
   curl -X POST http://tu-dominio.com/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"admin@example.com","password":"password"}'
   ```

2. **Usar el Token**:
   ```bash
   curl -X GET http://tu-dominio.com/api/mobile/products \
     -H "Authorization: Bearer {tu-token}" \
     -H "Accept: application/json"
   ```

3. **Crear una Venta**:
   ```bash
   curl -X POST http://tu-dominio.com/api/mobile/ventas \
     -H "Authorization: Bearer {tu-token}" \
     -H "Content-Type: application/json" \
     -d '{
       "branch_id": 1,
       "items": [
         {
           "product_id": 1,
           "quantity": 2,
           "precio_venta": 250.00
         }
       ]
     }'
   ```

---

## 📱 Próximos Pasos para la App Móvil

### 1. Configurar Capacitor

```bash
# Instalar Capacitor
npm install @capacitor/core @capacitor/cli

# Inicializar Capacitor
npx cap init

# Agregar plataforma Android
npm install @capacitor/android
npx cap add android

# Construir la aplicación web
npm run build

# Copiar assets a Android
npx cap copy android

# Abrir en Android Studio
npx cap open android
```

### 2. Configurar Cliente HTTP

Instalar Axios o usar Fetch API:

```bash
npm install axios
```

Crear servicio de API:

```javascript
// src/services/api.js
import axios from 'axios';

const API_URL = 'http://tu-dominio.com/api';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor para agregar token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

export default api;
```

### 3. Implementar Autenticación

```javascript
// src/composables/useAuth.js
import { ref } from 'vue';
import api from '@/services/api';

export const useAuth = () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('auth_token'));

  const login = async (email, password) => {
    const response = await api.post('/login', { email, password });
    token.value = response.data.data.token;
    user.value = response.data.data.user;
    localStorage.setItem('auth_token', token.value);
  };

  const logout = async () => {
    await api.post('/logout');
    token.value = null;
    user.value = null;
    localStorage.removeItem('auth_token');
  };

  return { user, token, login, logout };
};
```

### 4. Crear Servicios para cada Módulo

```javascript
// src/services/productService.js
import api from './api';

export const productService = {
  getAll: (params) => api.get('/mobile/products', { params }),
  getById: (id) => api.get(`/mobile/products/${id}`),
  create: (data) => api.post('/mobile/products', data),
  update: (id, data) => api.put(`/mobile/products/${id}`, data),
  delete: (id) => api.delete(`/mobile/products/${id}`),
  searchByBarcode: (barcode) => api.get(`/mobile/products/barcode/${barcode}`),
};
```

### 5. Implementar Almacenamiento Offline (Opcional)

```bash
npm install @capacitor/preferences
```

### 6. Agregar Notificaciones Push (Opcional)

```bash
npm install @capacitor/push-notifications
```

---

## 🔧 Mantenimiento

### Logs de API:
Los errores y actividades se registran automáticamente gracias a:
- Spatie Activity Log (ya configurado)
- Laravel Log (por defecto)

### Monitoreo:
Se recomienda instalar:
- Laravel Telescope (desarrollo)
- Laravel Horizon (colas)
- Sentry (producción)

---

## 📞 Soporte

Para cualquier duda o problema con la API:
1. Revisar la documentación en `docs/API_MOBILE.md`
2. Verificar los logs de Laravel en `storage/logs/`
3. Usar las herramientas de debug de Laravel (dd, dump, Log::info)

---

## ✨ Conclusión

La API REST está completamente configurada y lista para ser consumida por la aplicación móvil. Todos los endpoints están protegidos, validados y probados. El siguiente paso es configurar Capacitor y comenzar el desarrollo de la interfaz móvil.

**Fecha de Configuración**: 2024-12-21
**Versión**: 1.0.0
**Framework**: Laravel 11 + Laravel Passport
