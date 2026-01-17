# 📱 Ferretería TOTORO - API & Aplicación Móvil

## 🎯 Resumen del Proyecto

Aplicación web (Laravel + Inertia.js + Vue 3) convertida en aplicación móvil Android usando Capacitor. El sistema incluye una API REST completa para consumo desde la app móvil.

---

## ✅ Estado Actual de Configuración

### 🔧 Backend - API REST
- ✅ 7 Controladores API creados
- ✅ Autenticación OAuth 2.0 con Laravel Passport
- ✅ Rutas API configuradas y documentadas
- ✅ Modelos y relaciones actualizados
- ✅ Validaciones y permisos implementados
- ✅ Paginación y filtros en todos los endpoints

### 📱 Frontend Móvil - Capacitor
- ✅ Capacitor instalado y configurado
- ✅ Plataforma Android agregada
- ✅ Plugins esenciales instalados
- ✅ Servicios de API creados
- ✅ Composables de Vue para móvil
- ✅ Interceptores de axios configurados

---

## 📂 Estructura del Proyecto

```
tlapaleria/
├── app/
│   ├── Http/Controllers/Api/          # Controladores API
│   │   ├── AuthController.php
│   │   ├── ProductApiController.php
│   │   ├── InventarioApiController.php
│   │   ├── GastoApiController.php
│   │   ├── VentaApiController.php
│   │   ├── FaltanteApiController.php
│   │   └── BranchApiController.php
│   └── Models/
│       ├── Venta.php                  # Nuevo
│       └── VentaItem.php              # Nuevo
│
├── resources/js/
│   ├── services/                      # Servicios API
│   │   ├── api.js
│   │   ├── authService.js
│   │   ├── productService.js
│   │   ├── inventoryService.js
│   │   ├── ventaService.js
│   │   ├── gastoService.js
│   │   ├── faltanteService.js
│   │   ├── branchService.js
│   │   └── index.js
│   └── composables/
│       └── useMobileAuth.js           # Nuevo
│
├── routes/
│   └── api.php                        # Rutas API configuradas
│
├── android/                           # Proyecto Android (Capacitor)
│
├── docs/
│   ├── API_MOBILE.md                  # Documentación API completa
│   ├── API_SETUP_COMPLETE.md          # Resumen configuración API
│   └── CAPACITOR_SETUP.md             # Guía Capacitor
│
└── capacitor.config.json              # Configuración Capacitor
```

---

## 🚀 Inicio Rápido

### Requisitos Previos

- PHP 8.2+
- Node.js 16+
- Composer
- Android Studio (para desarrollo móvil)
- Java JDK 17

### 1. Instalación

```bash
# Clonar repositorio
git clone <repo-url>
cd tlapaleria

# Instalar dependencias PHP
composer install

# Instalar dependencias Node.js
npm install

# Configurar base de datos
cp .env.example .env
php artisan key:generate
php artisan migrate

# Instalar claves de Passport
php artisan passport:install
```

### 2. Desarrollo Web (Laravel + Inertia)

```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Vite dev server
npm run dev
```

Abrir: `http://localhost:8000`

### 3. Desarrollo Móvil (Capacitor + Android)

```bash
# Compilar assets web
npm run build

# Sincronizar con Android
npx cap sync android

# Abrir en Android Studio
npx cap open android

# En Android Studio:
# - Conectar dispositivo o iniciar emulador
# - Click en Run (▶️)
```

---

## 📚 Documentación Completa

### API REST
📖 **[docs/API_MOBILE.md](docs/API_MOBILE.md)**
- Todos los endpoints documentados
- Ejemplos de requests/responses
- Códigos de error
- Parámetros de filtrado

### Configuración API
📖 **[docs/API_SETUP_COMPLETE.md](docs/API_SETUP_COMPLETE.md)**
- Controladores creados
- Rutas configuradas
- Modelos y relaciones
- Próximos pasos

### Configuración Capacitor
📖 **[docs/CAPACITOR_SETUP.md](docs/CAPACITOR_SETUP.md)**
- Plugins instalados
- Comandos útiles
- Desarrollo Android
- Solución de problemas

---

## 🔐 Autenticación

### Flujo de Autenticación API

1. **Login**:
```bash
POST /api/login
{
  "email": "admin@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV1Qi..."
  }
}
```

2. **Uso del Token**:
```bash
GET /api/mobile/products
Headers:
  Authorization: Bearer {token}
  Accept: application/json
```

### En la App Móvil

```javascript
import { useMobileAuth } from '@/composables/useMobileAuth';

const { login, user, isAuthenticated } = useMobileAuth();

// Login
await login('admin@example.com', 'password');

// Usuario autenticado
console.log(user.value);

// Verificar autenticación
if (isAuthenticated.value) {
  // Usuario logueado
}
```

---

## 🛠️ Uso de Servicios

### Ejemplo: Listar Productos

```javascript
import { productService } from '@/services';

// Obtener todos los productos
const response = await productService.getAll({
  search: 'martillo',
  per_page: 20,
  page: 1
});

console.log(response.data);        // Array de productos
console.log(response.pagination);  // Metadata de paginación
```

### Ejemplo: Crear Venta

```javascript
import { ventaService } from '@/services';

const venta = await ventaService.create({
  branch_id: 1,
  items: [
    {
      product_id: 1,
      quantity: 2,
      precio_venta: 250.00
    },
    {
      product_id: 5,
      quantity: 1,
      precio_venta: 180.00
    }
  ]
});

console.log(venta.data); // Venta creada con cálculos automáticos
```

### Ejemplo: Ajustar Inventario

```javascript
import { inventoryService } from '@/services';

// Reducir stock (salida)
await inventoryService.adjustStock(1, -5, 'Venta manual');

// Aumentar stock (entrada)
await inventoryService.adjustStock(1, 10, 'Compra de mercancía');
```

---

## 🎨 Componentes Disponibles

### Servicios API

| Servicio | Propósito |
|----------|-----------|
| `authService` | Autenticación y gestión de sesión |
| `productService` | CRUD de productos |
| `inventoryService` | Gestión de inventario |
| `ventaService` | Registro de ventas |
| `gastoService` | Registro de gastos |
| `faltanteService` | Gestión de faltantes |
| `branchService` | Información de sucursales |

### Composables Vue

| Composable | Propósito |
|------------|-----------|
| `useMobileAuth` | Autenticación para app móvil |
| `useAuth` | Autenticación para web (existente) |

### Plugins Capacitor

| Plugin | Propósito |
|--------|-----------|
| `@capacitor/preferences` | Almacenamiento clave-valor |
| `@capacitor/network` | Estado de conexión |
| `@capacitor/camera` | Acceso a cámara |
| `@capacitor/splash-screen` | Pantalla de carga |

---

## 📋 Endpoints Principales

### Autenticación
- `POST /api/login` - Iniciar sesión
- `GET /api/me` - Usuario actual
- `POST /api/logout` - Cerrar sesión

### Productos
- `GET /api/mobile/products` - Listar
- `GET /api/mobile/products/{id}` - Ver
- `POST /api/mobile/products` - Crear
- `PUT /api/mobile/products/{id}` - Actualizar
- `DELETE /api/mobile/products/{id}` - Eliminar
- `GET /api/mobile/products/barcode/{barcode}` - Buscar por barcode

### Inventario
- `GET /api/mobile/inventory` - Listar
- `POST /api/mobile/inventory` - Crear
- `POST /api/mobile/inventory/{id}/adjust` - Ajustar stock

### Ventas
- `GET /api/mobile/ventas` - Listar
- `POST /api/mobile/ventas` - Crear
- `DELETE /api/mobile/ventas/{id}` - Eliminar

### Gastos
- `GET /api/mobile/gastos` - Listar
- `POST /api/mobile/gastos` - Crear
- `PUT /api/mobile/gastos/{id}` - Actualizar
- `DELETE /api/mobile/gastos/{id}` - Eliminar

### Faltantes
- `GET /api/mobile/faltantes` - Listar
- `POST /api/mobile/faltantes` - Crear
- `POST /api/mobile/faltantes/{id}/complete` - Marcar completado

### Sucursales
- `GET /api/mobile/branches` - Listar
- `GET /api/mobile/branches/{id}/stats` - Estadísticas

---

## 🔧 Comandos Útiles

### Laravel

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Verificar rutas API
php artisan route:list --path=api

# Crear token de Passport
php artisan passport:client --personal
```

### Capacitor

```bash
# Sincronizar todo
npx cap sync android

# Solo copiar assets
npx cap copy android

# Actualizar plugins
npx cap update android

# Abrir Android Studio
npx cap open android

# Ver logs
adb logcat | grep Capacitor
```

### NPM

```bash
# Desarrollo
npm run dev

# Producción
npm run build

# Limpiar node_modules
rm -rf node_modules && npm install
```

---

## 🐛 Solución de Problemas Comunes

### Error: "Token expired"
**Causa**: El token de Passport expiró
**Solución**: Hacer logout y login nuevamente

### Error: "CORS policy"
**Causa**: Configuración de CORS incorrecta
**Solución**: Verificar `config/cors.php` y middleware

### Error: "Network request failed"
**Causa**: No hay conexión o servidor apagado
**Solución**:
1. Verificar que Laravel esté corriendo
2. En emulador usar `http://10.0.2.2:8000`
3. En dispositivo usar IP de tu PC

### Error: "Cleartext HTTP not allowed"
**Causa**: Android no permite HTTP por defecto
**Solución**: Ya configurado en `capacitor.config.json` con `cleartext: true`

---

## 🚀 Próximos Pasos

### Desarrollo Inmediato

1. **Crear Vistas Móviles**
   - [ ] Pantalla de Login
   - [ ] Dashboard/Home
   - [ ] Listado de Productos
   - [ ] Detalle de Producto
   - [ ] Registrar Venta
   - [ ] Ver Inventario

2. **Navegación**
   - [ ] Implementar Bottom Navigation
   - [ ] Tabs principales
   - [ ] Drawer lateral (opcional)

3. **UX Móvil**
   - [ ] Loading states
   - [ ] Pull to refresh
   - [ ] Infinite scroll
   - [ ] Toast notifications
   - [ ] Confirmaciones de acciones

### Funcionalidades Avanzadas

4. **Escáner de Código de Barras**
   ```bash
   npm install @capacitor-community/barcode-scanner
   ```

5. **Modo Offline**
   - Implementar Vuex/Pinia para cache
   - Sincronizar cuando hay conexión

6. **Notificaciones Push**
   ```bash
   npm install @capacitor/push-notifications
   ```

7. **Geolocalización** (para tracking de sucursales)
   ```bash
   npm install @capacitor/geolocation
   ```

### Producción

8. **Optimización**
   - [ ] Lazy loading de componentes
   - [ ] Image optimization
   - [ ] Code splitting
   - [ ] Minificación

9. **Testing**
   - [ ] Unit tests (Jest)
   - [ ] E2E tests (Cypress)
   - [ ] API tests (PHPUnit)

10. **Deploy**
    - [ ] Configurar para producción
    - [ ] Generar APK firmado
    - [ ] Subir a Google Play Store

---

## 📞 Soporte y Recursos

### Documentación Interna
- [API_MOBILE.md](docs/API_MOBILE.md) - Referencia completa de API
- [API_SETUP_COMPLETE.md](docs/API_SETUP_COMPLETE.md) - Setup de backend
- [CAPACITOR_SETUP.md](docs/CAPACITOR_SETUP.md) - Setup de móvil

### Recursos Externos
- [Laravel Docs](https://laravel.com/docs)
- [Capacitor Docs](https://capacitorjs.com/docs)
- [Vue 3 Docs](https://vuejs.org/)
- [Inertia.js Docs](https://inertiajs.com/)

---

## 📊 Tecnologías Utilizadas

### Backend
- Laravel 11/12
- Laravel Passport (OAuth 2.0)
- MySQL/PostgreSQL
- PHP 8.2+

### Frontend Web
- Vue 3 (Composition API)
- Inertia.js
- Vite
- TailwindCSS

### Mobile
- Capacitor 8
- Android SDK
- Axios

---

## 📄 Licencia

[Tu licencia aquí]

---

## 👥 Equipo de Desarrollo

**Ferretería TOTORO - Sistema de Gestión Multi-Sucursal**

Desarrollado con ❤️ para optimizar las operaciones de ferretería.

---

**Última actualización**: 2024-12-21
**Versión**: 1.0.0
