# Implementación Multi-Tenant — TOTORO SaaS

> Documento técnico completo de la arquitectura multi-tenant implementada sobre el sistema de gestión Tlapalería.

---

## Índice

1. [Resumen ejecutivo](#1-resumen-ejecutivo)
2. [Paquete utilizado: stancl/tenancy](#2-paquete-utilizado-stancltenancy)
3. [Modelo Tenant](#3-modelo-tenant)
4. [Migraciones y columna tenant_id](#4-migraciones-y-columna-tenant_id)
5. [BelongsToTenant trait](#5-belongstotenant-trait)
6. [TenantScope global scope](#6-tenantscope-global-scope)
7. [Helper currentTenantId()](#7-helper-currenttenantid)
8. [Middleware ResolveTenant](#8-middleware-resolvetenant)
9. [Panel Super Admin](#9-panel-super-admin)
10. [Seeder de migración inicial](#10-seeder-de-migración-inicial)
11. [Tests de aislamiento](#11-tests-de-aislamiento)
12. [Flujo completo de una petición](#12-flujo-completo-de-una-petición)
13. [Bugs críticos encontrados y resueltos](#13-bugs-críticos-encontrados-y-resueltos)
14. [Configuración de entorno](#14-configuración-de-entorno)
15. [Referencia de archivos](#15-referencia-de-archivos)

---

## 1. Resumen ejecutivo

El sistema Tlapalería se convirtió de una aplicación monolítica de una sola empresa a una plataforma SaaS multi-tenant. El enfoque elegido es **single-database multi-tenancy**: todos los tenants comparten la misma base de datos, y el aislamiento se logra mediante la columna `tenant_id` en cada tabla y un **Global Scope de Eloquent** que filtra automáticamente todas las consultas.

### Ventajas del enfoque single-database
- Sin overhead de múltiples bases de datos ni conexiones dinámicas
- Migraciones centralizadas (un solo `php artisan migrate`)
- Fácil consulta cross-tenant para el super-admin
- Menor costo de hosting

### Conceptos clave
| Término | Descripción |
|---------|-------------|
| **Tenant** | Una empresa/ferretería cliente del SaaS. Identificada por un slug (ej. `tlapaleria`) |
| **tenant_id** | Columna FK string en todas las tablas que relaciona registros con su empresa |
| **Root user** | Usuario con `tenant_id = null`. Tiene acceso global a todos los tenants |
| **Contexto de tenant** | Estado activo cuando `tenancy()->initialize($tenant)` ha sido llamado |
| **TenantScope** | Global scope de Eloquent que filtra por `tenant_id` automáticamente |

---

## 2. Paquete utilizado: stancl/tenancy

**Versión instalada:** `stancl/tenancy ^3.10`

```bash
composer require stancl/tenancy
php artisan vendor:publish --tag=tenancy-config
php artisan vendor:publish --tag=tenancy-migrations
```

Se usa **únicamente el motor de resolución de tenants** de stancl (subdominios, contexto `tenancy()`). Los mecanismos de base de datos por tenant (`DatabaseTenancyBootstrapper`, `SwitchTenantDatabase`) no se usan — toda la lógica de aislamiento es propia.

### config/tenancy.php (extracto relevante)

```php
'tenant_model' => \App\Models\Tenant::class,

'bootstrappers' => [
    // DatabaseTenancyBootstrapper REMOVIDO — usamos single-database
    Stancl\Tenancy\Bootstrappers\CacheTagsBootstrapper::class,
],

'id_generator' => null, // IMPORTANTE: ver sección 13.1
```

---

## 3. Modelo Tenant

**Archivo:** `app/Models/Tenant.php`

```php
class Tenant extends BaseTenant
{
    use HasDomains;

    // CRÍTICO: evitar que GeneratesIds trate el slug como entero
    public $incrementing = false;
    protected $keyType = 'string';

    public function getIncrementing(): bool
    {
        return false;
    }

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'settings'      => 'array',
        'data'          => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return ['id', 'name', 'rfc', 'email', 'phone', 'logo',
                'status', 'plan', 'trial_ends_at', 'settings'];
    }

    public function isActive(): bool  { return in_array($this->status, ['active', 'trial']); }
    public function isTrial(): bool   { return $this->status === 'trial'; }
    public function isSuspended(): bool { return $this->status === 'suspended'; }
}
```

### Columnas del tenant (tabla `tenants`)

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | string PK | Slug del tenant (ej. `tlapaleria`) |
| `name` | string | Nombre legal de la empresa |
| `rfc` | string nullable | RFC fiscal |
| `email` | string | Email de contacto |
| `phone` | string nullable | Teléfono |
| `logo` | string nullable | Ruta al logo |
| `status` | enum | `trial`, `active`, `suspended`, `cancelled` |
| `plan` | enum | `free`, `starter`, `pro`, `enterprise` |
| `trial_ends_at` | datetime | Fin del período de prueba |
| `settings` | json | Configuraciones personalizadas del tenant |
| `data` | json | Columna interna de stancl para atributos virtuales |

---

## 4. Migraciones y columna tenant_id

Se crearon migraciones para agregar `tenant_id string nullable` a todas las tablas existentes:

| Migración | Tablas afectadas |
|-----------|-----------------|
| `2025_12_28_add_tenant_id_to_branches` | `branches` |
| `2025_12_28_add_tenant_id_to_users` | `users` |
| `2025_12_28_add_tenant_id_to_products` | `products` |
| `2025_12_28_add_tenant_id_to_customers` | `customers` |
| `2025_12_28_add_tenant_id_to_suppliers` | `suppliers` |
| `2025_12_28_add_tenant_id_to_sales` | `sales`, `sale_details` |
| `2025_12_28_add_tenant_id_to_purchases` | `purchases`, `purchase_details` |
| `2025_12_28_add_tenant_id_to_inventory` | `inventories`, `branch_inventory` |
| `2026_04_11_create_cuentas_por_cobrar` | `cuentas_por_cobrar`, `cobros` |
| `2026_04_11_create_cuentas_por_pagar` | `cuentas_por_pagar`, `pagos_proveedor` |

### Patrón de migración

```php
Schema::table('customers', function (Blueprint $table) {
    $table->string('tenant_id')->nullable()->after('id');
    $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
    $table->index('tenant_id');
});
```

---

## 5. BelongsToTenant trait

**Archivo:** `app/Traits/BelongsToTenant.php`

Este trait se aplica a **todos los modelos que necesitan aislamiento por tenant**. Hace dos cosas:

1. **Auto-asigna `tenant_id`** en el evento `creating` usando `currentTenantId()`
2. **Registra el TenantScope** como global scope en el evento `booted`

```php
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Auto-asignar tenant_id al crear
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = currentTenantId();
            }
        });

        // Registrar filtro automático
        static::addGlobalScope(new TenantScope());
    }

    // Escape hatch: Customer::withoutTenant()->get()
    public static function withoutTenant(): Builder
    {
        return static::withoutGlobalScope(TenantScope::class);
    }

    // Filtrar por tenant específico: Customer::forTenant('empresa-a')->get()
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }
}
```

### Modelos que usan el trait

```
Customer, Supplier, Product, Branch, Sale, Purchase,
Inventory, BranchInventory, Gasto, Faltante,
CuentaPorCobrar, CuentaPorPagar, Cobro, PagoProveedor
```

---

## 6. TenantScope global scope

**Archivo:** `app/Models/Scopes/TenantScope.php`

```php
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = currentTenantId();

        if ($tenantId !== null) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
        // Si tenantId es null → contexto root, sin filtro
    }
}
```

**Comportamiento:**

| Contexto | `currentTenantId()` retorna | Resultado |
|----------|----------------------------|-----------|
| Subdomain `tlapaleria.totoro.mx` | `'tlapaleria'` | Filtra solo registros de ese tenant |
| Usuario autenticado con `tenant_id` | ID del tenant del usuario | Filtra por ese tenant |
| Root user (sin tenant) | `null` | Sin filtro — ve todo |
| Sin autenticación | `null` | Sin filtro |

---

## 7. Helper currentTenantId()

**Archivo:** `app/helpers.php`

Resolución en cascada del tenant activo:

```php
function currentTenantId(): ?string
{
    // 1. Tenant inicializado por stancl via subdominio
    try {
        $tenancy = tenancy();
        if ($tenancy->initialized && $tenancy->tenant !== null) {
            return (string) $tenancy->tenant->getTenantKey(); // ← getTenantKey(), no ->id
        }
    } catch (\Throwable) {
        // tenancy() no disponible (migraciones, seeders)
    }

    // 2. Usuario autenticado
    $user = auth()->user() ?? auth('api')->user();
    if ($user) {
        return $user->tenant_id; // null si es root
    }

    return null;
}
```

> **Nota importante:** Se usa `getTenantKey()` en lugar de `->id` porque es el método del contrato de la interfaz `TenantContract`. Ver sección 13.2.

---

## 8. Middleware ResolveTenant

**Archivo:** `app/Http/Middleware/ResolveTenant.php`

Resuelve el tenant activo desde el subdominio de la URL o desde el header HTTP.

```php
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost(); // ej. "tlapaleria.totoro.mx"
        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            $slug = $parts[0]; // "tlapaleria"

            $tenant = Tenant::find($slug);
            if ($tenant) {
                tenancy()->initialize($tenant);
            }
        }

        // Fallback: header X-Tenant-ID (para API/Postman)
        if (!tenancy()->initialized) {
            $tenantId = $request->header('X-Tenant-ID');
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant) tenancy()->initialize($tenant);
            }
        }

        return $next($request);
    }
}
```

**Registro en** `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->prepend(\App\Http\Middleware\ResolveTenant::class);
})
```

---

## 9. Panel Super Admin

Accesible solo para usuarios con `role = 'root'` en la ruta `/admin/tenants`.

### Rutas

```php
Route::middleware(['auth', 'role:root'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/tenants',                    [TenantController::class, 'index'])         ->name('tenants.index');
        Route::post('/tenants',                   [TenantController::class, 'store'])         ->name('tenants.store');
        Route::put('/tenants/{id}',               [TenantController::class, 'update'])        ->name('tenants.update');
        Route::post('/tenants/{id}/suspend',      [TenantController::class, 'suspend'])       ->name('tenants.suspend');
        Route::post('/tenants/{id}/impersonate',  [TenantController::class, 'impersonate'])   ->name('tenants.impersonate');
        Route::post('/stop-impersonating',        [TenantController::class, 'stopImpersonating'])->name('stop-impersonating');
    });
```

### Funcionalidades del TenantController

| Acción | Descripción |
|--------|-------------|
| `index()` | Lista todos los tenants con conteo de usuarios y sucursales |
| `store()` | Crea un nuevo tenant (valida slug `[a-z0-9\-]+`) |
| `update()` | Actualiza nombre, RFC, email, plan, status, etc. |
| `suspend()` | Toggle suspended ↔ active |
| `impersonate()` | Inicia sesión como admin del tenant (guarda `original_user_id` en sesión) |
| `stopImpersonating()` | Restaura la sesión root original |

### Componente Vue

**Archivo:** `resources/js/Pages/Admin/Tenants/Index.vue`

- Tabla de tenants con badges de estado (color por status) y plan
- Modal inline para crear/editar tenants
- Campo slug solo editable en creación (no en edición)
- Campo `trial_ends_at` visible solo cuando `status = 'trial'`
- Botones Suspender/Activar e Ingresar (impersonate)

### Sidebar condicional

En `resources/js/Components/Sidebar.vue`, la sección "Super Admin" solo aparece si el usuario tiene `role = 'root'`:

```js
const isRoot = computed(() => usePage().props.auth.user?.role === 'root')

// En el array de secciones:
...(isRoot.value ? [{
    title: 'Super Admin',
    items: [{ name: 'Empresas', route: 'admin.tenants.index', ... }],
}] : []),
```

---

## 10. Seeder de migración inicial

**Archivo:** `database/seeders/InitialTenantSeeder.php`

Script de migración única para convertir datos existentes pre-SaaS al modelo multi-tenant:

```bash
php artisan db:seed --class=InitialTenantSeeder
```

### Proceso

1. Crea (o recupera) el tenant inicial con slug `tlapaleria`
2. Identifica usuarios con rol `root` (via `role_user` JOIN `roles`) — estos quedan con `tenant_id = null`
3. Asigna `tenant_id = 'tlapaleria'` a todos los registros sin tenant en:
   - `users` (no-root), `branches`, `customers`, `suppliers`, `products`, `categories`
   - `category_product`, `product_supplier` (pivots)
   - `sales`, `purchases`, `gastos`, `faltantes`, `branch_inventory`, `inventories`
   - `sale_details`, `purchase_details`, `cobros`, `pagos_proveedor`
   - `cuentas_por_cobrar`, `cuentas_por_pagar`

Todo dentro de una transacción — hace rollback si algo falla.

### Script de deployment

**Archivo:** `scripts/migrate-to-saas.sh`

```bash
./scripts/migrate-to-saas.sh           # Migración real
./scripts/migrate-to-saas.sh --dry-run # Solo verifica, sin cambios
./scripts/migrate-to-saas.sh --rollback # Revierte (pone tenant_id = null en todos)
```

---

## 11. Tests de aislamiento

**Archivo:** `tests/Feature/TenantIsolationTest.php`

11 tests automáticos que verifican el correcto funcionamiento del aislamiento:

| Test | Descripción |
|------|-------------|
| `current_tenant_id_returns_tenant_key_after_initialize` | Verifica que `currentTenantId()` retorna el slug correcto |
| `tenant_a_cannot_see_tenant_b_customers` | Tenant A solo ve sus clientes |
| `tenant_b_cannot_see_tenant_a_customers` | Tenant B solo ve sus clientes |
| `root_user_can_see_all_customers_without_scope` | Root ve todos con `withoutTenant()` |
| `creating_record_in_tenant_context_auto_assigns_tenant_id` | El trait auto-asigna tenant_id |
| `creating_record_without_tenant_context_leaves_tenant_id_null` | Sin contexto, tenant_id queda null |
| `branches_are_isolated_per_tenant` | Sucursales aisladas por tenant |
| `without_tenant_scope_bypasses_filter` | `withoutTenant()` levanta el scope |
| `suspended_tenant_status_is_detected` | `isSuspended()` detecta estado correcto |
| `active_tenant_is_detected` | `isActive()` detecta estado correcto |
| `for_tenant_scope_filters_by_specific_tenant` | `forTenant('x')` filtra correctamente |

### Ejecución

```bash
php artisan test tests/Feature/TenantIsolationTest.php
```

**Resultado esperado:** `11 tests, 11 assertions` — todos en verde.

---

## 12. Flujo completo de una petición

```
Petición HTTP → tlapaleria.totoro.mx/customers

1. ResolveTenant middleware (prepended)
   ├── Extrae "tlapaleria" del subdominio
   ├── Busca Tenant::find('tlapaleria')
   └── tenancy()->initialize($tenant) ← activa el contexto

2. Auth middleware verifica sesión

3. CustomerController@index
   └── Customer::all()
       ├── TenantScope::apply() detecta currentTenantId() = 'tlapaleria'
       └── SQL: SELECT * FROM customers WHERE tenant_id = 'tlapaleria'

4. Respuesta solo con datos del tenant 'tlapaleria'
```

---

## 13. Bugs críticos encontrados y resueltos

### 13.1 tenant_id = 0 en todos los inserts

**Síntoma:** Todos los registros se insertaban con `tenant_id = 0` en vez del slug string.

**Causa raíz:** El trait `GeneratesIds` de stancl sobrescribe `getIncrementing()` así:

```php
// Dentro de Stancl\Tenancy\Database\Concerns\GeneratesIds
public function getIncrementing(): bool
{
    return ! app()->bound(UniqueIdentifierGenerator::class);
}
```

Con `'id_generator' => null` en `config/tenancy.php`, no se bindea ningún `UniqueIdentifierGenerator`. Por lo tanto, `getIncrementing()` retorna `true`. Eloquent entonces trata el slug `'empresa-a'` como un entero auto-incremental → cast a `(int) 0`.

**Solución:** Sobrescribir explícitamente en `App\Models\Tenant`:

```php
public $incrementing = false;
protected $keyType = 'string';

public function getIncrementing(): bool
{
    return false;
}
```

### 13.2 app()->bound('tenancy') siempre falso

**Síntoma:** El check `if (app()->bound('tenancy'))` en `helpers.php` retornaba `false`, por lo que `currentTenantId()` nunca resolvía el tenant de stancl.

**Causa raíz:** stancl registra el singleton como `Tenancy::class` (el FQCN), no como el string `'tenancy'`. El helper `tenancy()` hace `app(Tenancy::class)` internamente.

**Solución:**

```php
// Antes (incorrecto):
if (app()->bound('tenancy')) { ... }

// Después (correcto):
try {
    $tenancy = tenancy(); // usa app(Tenancy::class) internamente
    if ($tenancy->initialized && $tenancy->tenant !== null) {
        return (string) $tenancy->tenant->getTenantKey();
    }
} catch (\Throwable) { /* no disponible en seeders/migraciones */ }
```

### 13.3 Desincronización de secuencia PostgreSQL en branches

**Síntoma:** La migración seed `2025_12_22_000000_seed_initial_data.php` inserta una sucursal con `id = 1` explícito. PostgreSQL no actualiza la secuencia automáticamente al insertar PK manualmente, por lo que el siguiente `INSERT` también intenta usar `id = 1` → violación de PK única.

**Solución en tests:**

```php
\DB::statement("SELECT setval('branches_id_seq', COALESCE((SELECT MAX(id) FROM branches), 0))");
```

**Solución en producción:** Ejecutar este `setval` después de cualquier seeder que use PKs explícitas.

### 13.4 PHPUnit 11: @test depreciado

**Síntoma:** `PHPUnit\Framework\Warning: The #[PHPUnit\Framework\Attributes\Test] attribute should be used instead of the @test annotation`.

**Solución:** Reemplazar todos los doc-comment `/** @test */` con el atributo PHP 8:

```php
// Antes:
/** @test */
public function my_test(): void { ... }

// Después:
#[Test]
public function my_test(): void { ... }
```

---

## 14. Configuración de entorno

### Variables .env requeridas

```env
# Base de datos principal
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tlapaleria
DB_USERNAME=postgres
DB_PASSWORD=tu_password

# Sin configuración especial de tenancy — todo en la misma BD
```

### Configuración de subdominios (Laragon local)

Para probar con subdominios en entorno local con Laragon:

1. Editar `C:\Windows\System32\drivers\etc\hosts` como administrador:
   ```
   127.0.0.1   tlapaleria.test
   127.0.0.1   empresa-demo.test
   ```

2. En Laragon, el virtual host de Apache/Nginx debe incluir soporte para subdominios. Crear `tlapaleria.test.conf` en la carpeta de configuración de Apache de Laragon.

3. En `config/tenancy.php`, configurar el dominio central:
   ```php
   'central_domains' => ['totoro.mx', 'localhost', '127.0.0.1'],
   ```

### phpunit.xml (testing)

```xml
<env name="DB_CONNECTION" value="pgsql"/>
<env name="DB_HOST" value="127.0.0.1"/>
<env name="DB_PORT" value="5432"/>
<env name="DB_DATABASE" value="tlapaleria_test"/>
<env name="DB_USERNAME" value="postgres"/>
<env name="DB_PASSWORD" value="pasword.123"/>
```

Crear la base de datos de testing antes de correr los tests:
```bash
php -r "
\$pdo = new PDO('pgsql:host=127.0.0.1;port=5432', 'postgres', 'pasword.123');
\$pdo->exec('CREATE DATABASE tlapaleria_test');
"
```

---

## 15. Referencia de archivos

| Archivo | Función |
|---------|---------|
| `app/Models/Tenant.php` | Modelo del tenant — slug PK, métodos isActive/isSuspended |
| `app/Models/Scopes/TenantScope.php` | Global scope que filtra queries por tenant_id |
| `app/Traits/BelongsToTenant.php` | Trait para modelos multi-tenant: auto-asigna tenant_id y registra TenantScope |
| `app/helpers.php` | `currentTenantId()` y `currentTenant()` helpers globales |
| `app/Http/Middleware/ResolveTenant.php` | Middleware que resuelve tenant desde subdominio o header |
| `app/Http/Controllers/Admin/TenantController.php` | CRUD de tenants + suspend + impersonate |
| `resources/js/Pages/Admin/Tenants/Index.vue` | Panel Super Admin de gestión de tenants |
| `resources/js/Components/Sidebar.vue` | Menú lateral con sección Super Admin condicional |
| `database/seeders/InitialTenantSeeder.php` | Migración one-time de datos existentes al modelo multi-tenant |
| `scripts/migrate-to-saas.sh` | Script de deployment SaaS con dry-run y rollback |
| `tests/Feature/TenantIsolationTest.php` | Suite de 11 tests de aislamiento por tenant |
| `config/tenancy.php` | Configuración de stancl/tenancy |
| `phpunit.xml` | Configuración de tests con PostgreSQL |
| `database/migrations/2025_12_28_*` | Migraciones que agregan tenant_id a tablas existentes |
| `database/migrations/2026_04_11_*` | Migraciones para cuentas por cobrar/pagar con tenant_id |
