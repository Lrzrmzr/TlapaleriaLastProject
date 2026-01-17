# Sistema de Sucursales - Ferretería TOTORO

## 📋 Índice
- [Introducción](#introducción)
- [Arquitectura](#arquitectura)
- [Base de Datos](#base-de-datos)
- [Modelos](#modelos)
- [Roles y Permisos](#roles-y-permisos)
- [Soft Deletes](#soft-deletes)
- [Uso y Flujo de Trabajo](#uso-y-flujo-de-trabajo)
- [Migraciones](#migraciones)

## Introducción

El sistema de sucursales permite gestionar múltiples ubicaciones físicas de la ferretería, cada una con su propio inventario independiente y personal asignado. El sistema implementa **soft deletes** en todas las tablas para asegurar que NINGÚN dato se elimine permanentemente de la base de datos.

### Características Principales

- ✅ Inventario independiente por sucursal
- ✅ Personal asignado a sucursales específicas
- ✅ Roles jerárquicos (Super Admin, Branch Admin, Branch Staff, etc.)
- ✅ Soft deletes en TODAS las tablas (no se elimina nada permanentemente)
- ✅ Control de acceso basado en sucursal
- ✅ Sucursal principal designada
- ✅ Estados activo/inactivo para todo

## Arquitectura

### Jerarquía de Roles

```
Super Admin (Administrador Global)
    ├── Acceso total al sistema
    ├── Gestión de todas las sucursales
    ├── Creación de nuevas sucursales
    └── Asignación de administradores de sucursal

Branch Admin (Administrador de Sucursal)
    ├── Acceso completo a SU sucursal
    ├── Gestión de personal de su sucursal
    ├── Gestión de inventario de su sucursal
    └── Reportes de su sucursal

Branch Staff (Empleado de Sucursal)
    ├── Acceso limitado a su sucursal
    ├── Operaciones básicas (ventas, compras)
    └── Sin acceso a configuración

Cashier (Cajero)
    └── Solo acceso a módulo de ventas

Warehouse (Almacenista)
    └── Solo acceso a inventario y compras
```

## Base de Datos

### Tabla: `branches` (Sucursales)

```sql
CREATE TABLE branches (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    manager_name VARCHAR(100),
    active BOOLEAN DEFAULT TRUE,
    is_main BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL  -- Soft delete
);
```

### Tabla: `branch_inventory` (Inventario por Sucursal)

```sql
CREATE TABLE branch_inventory (
    id BIGINT UNSIGNED PRIMARY KEY,
    branch_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED,
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 0,
    max_stock INT DEFAULT 0,
    cost DECIMAL(10,2),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,  -- Soft delete
    UNIQUE(branch_id, product_id)
);
```

### Cambios en Tabla: `users`

```sql
ALTER TABLE users ADD COLUMN:
    branch_id BIGINT UNSIGNED NULL,  -- Sucursal asignada
    active BOOLEAN DEFAULT TRUE,      -- Estado del usuario
    deleted_at TIMESTAMP NULL         -- Soft delete
```

### Cambios en Tablas de Transacciones

Las siguientes tablas ahora incluyen `branch_id`:
- `sales` - Ventas por sucursal
- `purchases` - Compras por sucursal
- `faltantes` - Faltantes por sucursal
- `gastos` - Gastos por sucursal

## Modelos

### Branch Model

```php
<?php
namespace App\Models;

class Branch extends Model
{
    use SoftDeletes;

    // Relaciones
    public function users(): HasMany
    public function inventory(): HasMany
    public function products(): BelongsToMany
    public function sales(): HasMany
    public function purchases(): HasMany

    // Scopes
    public function scopeActive($query)
    public function scopeMain($query)

    // Métodos útiles
    public function hasProduct($productId): bool
    public function getProductStock($productId): int
    public function getTotalStockAttribute()
    public function getTotalInventoryValueAttribute()
}
```

### BranchInventory Model

```php
<?php
namespace App\Models;

class BranchInventory extends Model
{
    use SoftDeletes;

    // Métodos
    public function isLowStock(): bool
    public function isOutOfStock(): bool
    public function addStock(int $quantity): void
    public function reduceStock(int $quantity): void
    public function getValueAttribute(): float
}
```

### User Model (Actualizado)

```php
<?php
namespace App\Models;

class User extends Authenticatable
{
    use SoftDeletes;

    // Nuevos métodos
    public function branch(): BelongsTo
    public function isSuperAdmin(): bool
    public function isBranchAdmin(): bool
    public function isBranchStaff(): bool
    public function scopeActive($query)
    public function scopeForBranch($query, $branchId)
}
```

## Roles y Permisos

### Roles del Sistema

| Rol | Nombre Interno | Descripción |
|-----|---------------|-------------|
| Super Administrador | `super_admin` | Acceso total al sistema |
| Administrador de Sucursal | `branch_admin` | Admin de su sucursal |
| Empleado de Sucursal | `branch_staff` | Empleado con acceso limitado |
| Cajero | `cashier` | Solo módulo de ventas |
| Almacenista | `warehouse` | Solo inventario y compras |

### Verificación de Roles

```php
// En controladores o vistas
if ($user->isSuperAdmin()) {
    // Acceso total
}

if ($user->isBranchAdmin()) {
    // Acceso de admin a su sucursal
}

// Verificar sucursal
if ($user->branch_id === $branch->id) {
    // Usuario pertenece a esta sucursal
}
```

## Soft Deletes

### Concepto

**NADA se elimina permanentemente de la base de datos.** Todas las "eliminaciones" son lógicas usando:
- Campo `deleted_at` (soft delete de Laravel)
- Campo `active` (estado activo/inactivo)

### Tablas con Soft Delete

✅ `users`
✅ `branches`
✅ `products`
✅ `suppliers`
✅ `customers`
✅ `categories`
✅ `sales`
✅ `purchases`
✅ `inventories`
✅ `branch_inventory`
✅ `gastos`
✅ `faltantes`

### Uso en Controladores

```php
// ❌ NUNCA hacer esto
$product->forceDelete();  // Eliminación permanente - PROHIBIDO

// ✅ SIEMPRE hacer esto
$product->update(['active' => false]);  // Desactivar
$product->delete();  // Soft delete

// Restaurar
$product->restore();
$product->update(['active' => true]);

// Consultar incluyendo eliminados
Product::withTrashed()->get();

// Consultar solo eliminados
Product::onlyTrashed()->get();

// Consultar solo activos
Product::active()->get();
```

## Uso y Flujo de Trabajo

### 1. Creación de Sucursal

```php
// Como Super Admin
$branch = Branch::create([
    'name' => 'Ferretería TOTORO - Sucursal Norte',
    'code' => 'NORTE',
    'address' => 'Calle Principal #123',
    'phone' => '555-1234',
    'email' => 'norte@tlapaleria.com',
    'manager_name' => 'Juan Pérez',
    'active' => true,
    'is_main' => false,
]);
```

### 2. Asignar Usuario a Sucursal

```php
$user->update([
    'branch_id' => $branch->id,
]);

// Asignar rol
$user->roles()->attach($branchAdminRole);
```

### 3. Gestionar Inventario por Sucursal

```php
// Agregar producto al inventario de una sucursal
BranchInventory::create([
    'branch_id' => $branch->id,
    'product_id' => $product->id,
    'stock' => 100,
    'min_stock' => 10,
    'max_stock' => 500,
    'cost' => 25.50,
    'active' => true,
]);

// Ajustar stock
$inventory = BranchInventory::where('branch_id', $branchId)
    ->where('product_id', $productId)
    ->first();

$inventory->addStock(50);  // Agregar
$inventory->reduceStock(10);  // Reducir
```

### 4. Consultas por Sucursal

```php
// Obtener inventario de una sucursal
$inventory = BranchInventory::forBranch($branchId)
    ->active()
    ->with('product')
    ->get();

// Obtener ventas de una sucursal
$sales = Sale::where('branch_id', $branchId)
    ->whereBetween('created_at', [$start, $end])
    ->get();

// Obtener usuarios de una sucursal
$users = User::forBranch($branchId)
    ->active()
    ->get();
```

### 5. Middleware de Control de Acceso

```php
// En routes/web.php
Route::middleware(['auth', 'check.branch.access'])->group(function () {
    // Rutas que requieren acceso a sucursal
});

// El middleware automáticamente:
// - Permite acceso total a Super Admin
// - Restringe a usuarios sin sucursal
// - Agrega user_branch_id al request
```

## Migraciones

### Orden de Ejecución

1. `2024_12_20_000001_create_branches_table.php`
2. `2024_12_20_000002_add_branch_id_to_users_table.php`
3. `2024_12_20_000003_create_branch_inventory_table.php`
4. `2024_12_20_000004_add_soft_deletes_to_all_tables.php`
5. `2024_12_20_000005_add_branch_id_to_transactions_tables.php`

### Comandos

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeder
php artisan db:seed --class=BranchesAndRolesSeeder

# Rollback si es necesario
php artisan migrate:rollback --step=5
```

## Datos Iniciales

El seeder crea:
- ✅ 1 Sucursal Principal (MATRIZ)
- ✅ 5 Roles del sistema
- ✅ Códigos únicos para cada sucursal

## Consideraciones Importantes

### ⚠️ Reglas de Negocio

1. **Sucursal Principal**
   - Solo puede haber UNA sucursal principal
   - No se puede desactivar ni eliminar
   - Se crea automáticamente con el seeder

2. **Usuarios**
   - Los usuarios deben tener una sucursal asignada (excepto Super Admin)
   - No se puede eliminar una sucursal con usuarios activos
   - Usuarios inactivos no pueden iniciar sesión

3. **Inventario**
   - Cada sucursal tiene su propio inventario independiente
   - Un mismo producto puede tener diferentes stocks en diferentes sucursales
   - No se comparte stock entre sucursales

4. **Transacciones**
   - Todas las ventas, compras, gastos y faltantes se vinculan a una sucursal
   - Los reportes se filtran por sucursal
   - Super Admin ve todo, Branch Admin solo su sucursal

5. **Soft Deletes**
   - NUNCA usar `forceDelete()`
   - Siempre usar `delete()` para soft delete
   - Usar `active = false` para desactivar sin eliminar

## API Endpoints

### Sucursales

```
GET    /sucursales                    - Listar sucursales
POST   /sucursales                    - Crear sucursal
PUT    /sucursales/{id}               - Actualizar sucursal
DELETE /sucursales/{id}               - Desactivar sucursal (soft delete)
POST   /sucursales/{id}/restore       - Restaurar sucursal
POST   /sucursales/{id}/toggle-status - Cambiar estado activo/inactivo
GET    /sucursales/{id}/inventory     - Ver inventario de sucursal
GET    /sucursales/{id}/statistics    - Estadísticas de sucursal
GET    /sucursales-active             - Listar solo sucursales activas
```

## Próximos Pasos

1. ✅ Crear vistas Vue para gestión de sucursales
2. ✅ Agregar selector de sucursal en formularios
3. ✅ Implementar filtros por sucursal en todos los módulos
4. ✅ Crear reportes por sucursal
5. ✅ Implementar transferencias entre sucursales (futuro)

---

**Nota:** Este sistema está diseñado para escalar. Puedes agregar tantas sucursales como necesites sin cambios en el código.
