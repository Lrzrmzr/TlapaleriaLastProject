# Verificación de Conectividad con Base de Datos

## 📋 Estado de Migraciones

### Migraciones Disponibles (en orden de ejecución):

✅ **Tablas Base del Sistema:**
1. `0001_01_01_000000_create_users_table.php` - Usuarios
2. `0001_01_01_000001_create_cache_table.php` - Caché
3. `0001_01_01_000002_create_jobs_table.php` - Jobs/Colas

✅ **Tablas de Gestión de Usuarios:**
4. `2025_08_09_000001_create_roles_table.php` - Roles
5. `2025_08_09_000003_create_role_user_table.php` - Relación Usuario-Rol

✅ **Tablas de Catálogos:**
6. `2025_08_09_000004_create_suppliers_table.php` - Proveedores
7. `2025_08_09_000005_create_products_table.php` - Productos
8. `2025_08_13_000018_create_product_supplier_table.php` - Relación Producto-Proveedor (Many-to-Many)

✅ **Tablas de Inventario:**
9. `2025_08_09_000006_create_inventories_table.php` - Inventarios

✅ **Tablas de Ventas:**
10. `2025_08_09_000007_create_customers_table.php` - Clientes
11. `2025_08_09_000008_create_sales_table.php` - Ventas
12. `2025_08_09_000009_create_sale_details_table.php` - Detalle de Ventas
13. `2025_08_12_000016_add_libre_fields_to_sale_details_table.php` - Campos para ventas libres
14. `2025_08_12_000017_improve_sale_details_for_ventas_libres.php` - Mejoras ventas libres

✅ **Tablas de Compras:**
15. `2025_08_09_000010_create_purchases_table.php` - Compras
16. `2025_08_09_000011_create_purchase_details_table.php` - Detalle de Compras

✅ **Tablas de Gestión Operativa:**
17. `2025_08_10_000012_create_faltantes_table.php` - Productos Faltantes
18. `2025_08_11_000014_add_confirmado_to_faltantes_table.php` - Campo confirmado
19. `2025_08_11_000013_create_gastos_table.php` - Gastos
20. `2025_08_11_000015_add_total_to_gastos_table.php` - Campo total

---

## 🔗 Verificación de Controladores y Rutas

### 1. **Proveedores (Suppliers)**
- **Controlador:** `SupplierController.php` ✅
- **Rutas:**
  - GET `/proveedores` → index() ✅
  - POST `/proveedores` → store() ✅
  - PUT `/proveedores/{id}` → update() ✅
  - DELETE `/proveedores/{id}` → destroy() ✅
- **Modelo:** `Supplier.php` ✅
- **Relaciones:**
  - `productsSupplied()` - BelongsToMany con Product ✅
- **Vista:** `Proveedores/Index.vue` ✅

### 2. **Productos (Products)**
- **Controlador:** `ProductController.php` ✅
- **Rutas:**
  - GET `/productos` → index() ✅
  - POST `/productos` → store() ✅
  - PUT `/productos/{id}` → update() ✅
  - DELETE `/productos/{id}` → destroy() ✅
  - POST `/productos/{producto}/proveedores` → attachSupplier() ✅
  - PUT `/productos/{producto}/proveedores/{supplier}` → updateSupplier() ✅
  - DELETE `/productos/{producto}/proveedores/{supplier}` → detachSupplier() ✅
- **Modelo:** `Product.php` ✅
- **Relaciones:**
  - `supplier()` - BelongsTo con Supplier ✅
  - `suppliers()` - BelongsToMany con Supplier ✅
  - `inventory()` - HasMany con Inventory ✅
  - `saleDetails()` - HasMany con SaleDetail ✅
- **Vista:** `Productos/Index.vue` ✅

### 3. **Inventario (Inventory)**
- **Controlador:** `InventoryController.php` ✅
- **Rutas:**
  - GET `/inventario` → index() ✅
  - POST `/inventario` → store() ✅
  - PUT `/inventario/{id}` → update() ✅
  - DELETE `/inventario/{id}` → destroy() ✅
- **Modelo:** `Inventory.php` ✅
- **Relaciones:**
  - `product()` - BelongsTo con Product ✅
- **Vista:** `Inventario/Index.vue` ✅

### 4. **Ventas (Sales)**
- **Controlador:** `SaleController.php` ✅
- **Rutas:**
  - GET `/ventas` → index() ✅
  - POST `/ventas` → store() ✅
  - GET `/ventas/{id}` → show() ✅
  - DELETE `/ventas/{id}` → destroy() ✅
- **Modelo:** `Sale.php` ✅
- **Relaciones:**
  - `user()` - BelongsTo con User ✅
  - `customer()` - BelongsTo con Customer ✅
  - `saleDetails()` - HasMany con SaleDetail ✅
- **Vista:** `Ventas/Index.vue` ✅

### 5. **Faltantes (Missing Products)**
- **Controlador:** `FaltanteController.php` ✅
- **Rutas:**
  - GET `/faltantes` → index() ✅
  - POST `/faltantes` → store() ✅
  - PUT `/faltantes/{id}` → update() ✅
  - DELETE `/faltantes/{id}` → destroy() ✅
- **Modelo:** `Faltante.php` ✅
- **Relaciones:**
  - `product()` - BelongsTo con Product ✅
  - `user()` - BelongsTo con User ✅
- **Vista:** `Faltantes/Index.vue` ✅

### 6. **Gastos (Expenses)**
- **Controlador:** `GastoController.php` ✅
- **Rutas:**
  - GET `/gastos` → index() ✅
  - POST `/gastos` → store() ✅
  - PUT `/gastos/{id}` → update() ✅
  - DELETE `/gastos/{id}` → destroy() ✅
- **Modelo:** `Gasto.php` ✅
- **Relaciones:**
  - `user()` - BelongsTo con User ✅
- **Vista:** `Gastos/Index.vue` ✅

### 7. **Usuarios (Users)**
- **Controlador:** `UserController.php` ✅
- **Rutas:**
  - GET `/usuarios` → index() ✅
  - POST `/usuarios` → store() ✅
  - PUT `/usuarios/{id}` → update() ✅
  - DELETE `/usuarios/{id}` → destroy() ✅
- **Modelo:** `User.php` ✅
- **Relaciones:**
  - `roles()` - BelongsToMany con Role ✅
  - `sales()` - HasMany con Sale ✅
  - `faltantes()` - HasMany con Faltante ✅
  - `gastos()` - HasMany con Gasto ✅
- **Vista:** `Usuarios/Index.vue` ✅

---

## 🔍 Puntos a Verificar Antes de Usar

### 1. **Versión de PHP**
⚠️ **IMPORTANTE:** El proyecto requiere PHP >= 8.2.0
- Actualmente detecté: PHP 8.1.0
- **Acción requerida:** Actualizar PHP en Laragon a versión 8.2 o superior

### 2. **Configuración de Base de Datos**
Verificar archivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tlapaleria
DB_USERNAME=root
DB_PASSWORD=
```

### 3. **Ejecutar Migraciones**
Una vez actualizado PHP, ejecutar:
```bash
php artisan migrate
```

### 4. **Datos de Prueba (Opcional)**
Para probar con datos, puedes:
1. **Manualmente:** Usar las interfaces creadas para agregar datos
2. **Seeders:** Crear seeders para datos de prueba

---

## 📊 Diagrama de Relaciones

```
Users (usuarios)
├── roles (Many-to-Many via role_user)
├── sales (One-to-Many)
├── faltantes (One-to-Many)
└── gastos (One-to-Many)

Suppliers (proveedores)
└── products (Many-to-Many via product_supplier)

Products (productos)
├── supplier (Many-to-One) [proveedor principal]
├── suppliers (Many-to-Many via product_supplier) [múltiples proveedores]
├── inventory (One-to-Many)
└── saleDetails (One-to-Many)

Sales (ventas)
├── user (Many-to-One)
├── customer (Many-to-One)
└── saleDetails (One-to-Many)

Inventory (inventarios)
└── product (Many-to-One)

Faltantes
├── product (Many-to-One)
└── user (Many-to-One)

Gastos
└── user (Many-to-One)
```

---

## ✅ Checklist de Verificación

Antes de usar el sistema:

- [ ] Actualizar PHP a versión 8.2 o superior en Laragon
- [ ] Verificar configuración de `.env`
- [ ] Ejecutar `composer install`
- [ ] Ejecutar `php artisan migrate`
- [ ] Verificar que todas las tablas se crearon correctamente
- [ ] Crear un usuario administrador
- [ ] Probar cada módulo:
  - [ ] Crear un proveedor
  - [ ] Crear un producto (con proveedor)
  - [ ] Agregar múltiples proveedores a un producto
  - [ ] Registrar inventario
  - [ ] Realizar una venta
  - [ ] Registrar un faltante
  - [ ] Registrar un gasto
  - [ ] Gestionar usuarios

---

## 🧪 Comandos de Verificación

Una vez actualizado PHP:

```bash
# Ver estado de migraciones
php artisan migrate:status

# Verificar conexión a BD
php artisan tinker
>>> DB::connection()->getPdo()

# Listar todas las rutas
php artisan route:list

# Verificar modelos
php artisan tinker
>>> App\Models\Product::count()
>>> App\Models\Supplier::count()
```

---

## 📝 Notas Importantes

1. **Integridad Referencial:** Todas las relaciones usan `onDelete('cascade')` o verificaciones antes de eliminar
2. **Validaciones:** Todos los controladores tienen validación de datos
3. **Pivot Table:** La tabla `product_supplier` permite múltiples proveedores por producto con datos adicionales (costo, código, preferido, etc.)
4. **Ventas Libres:** El sistema soporta ventas de productos no registrados en inventario
5. **Soft Deletes:** No implementado actualmente - eliminaciones son permanentes

---

## 🚀 Próximos Pasos Recomendados

1. Actualizar PHP a 8.2+
2. Ejecutar migraciones
3. Crear datos de prueba básicos
4. Probar flujo completo: Proveedor → Producto → Inventario → Venta
5. Verificar que las estadísticas del dashboard se calculan correctamente
