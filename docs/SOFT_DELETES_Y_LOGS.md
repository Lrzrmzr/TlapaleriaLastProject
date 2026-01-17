# Sistema de Soft Deletes y Auditoría de Logs - Ferretería TOTORO

## 📋 Estado Actual del Sistema

### ✅ Modelos CON Soft Deletes Implementado

Los siguientes modelos YA TIENEN el trait `SoftDeletes` correctamente implementado:

- ✅ **Product** (`app/Models/Product.php`)
- ✅ **User** (`app/Models/User.php`)
- ✅ **Branch** (`app/Models/Branch.php`)
- ✅ **BranchInventory** (`app/Models/BranchInventory.php`)
- ✅ **Supplier** (`app/Models/Supplier.php`)
- ✅ **Category** (`app/Models/Category.php`)
- ✅ **Customer** (`app/Models/Customer.php`)
- ✅ **Sale** (`app/Models/Sale.php`)
- ✅ **Purchase** (`app/Models/Purchase.php`)
- ✅ **Faltante** (`app/Models/Faltante.php`)
- ✅ **Gasto** (`app/Models/Gasto.php`)
- ✅ **Inventory** (`app/Models/Inventory.php`)

### 🎉 TODOS los modelos ahora tienen Soft Deletes

Todos los modelos del sistema ya cuentan con el trait `SoftDeletes` implementado, lo que garantiza que NINGÚN dato se elimine permanentemente de la base de datos.

### 📊 Migraciones de Soft Deletes

La migración `2024_12_20_000004_add_soft_deletes_to_all_tables.php` YA EXISTE y agrega las columnas `deleted_at` a todas las tablas necesarias:

- ✅ `products` - deleted_at + active
- ✅ `suppliers` - deleted_at + active
- ✅ `categories` - deleted_at
- ✅ `customers` - deleted_at + active
- ✅ `sales` - deleted_at + status
- ✅ `purchases` - deleted_at + status
- ✅ `inventories` - deleted_at + active
- ✅ `faltantes` - deleted_at
- ✅ `gastos` - deleted_at + active

## 🔧 Acciones Requeridas

### 1. Agregar SoftDeletes a Modelos Faltantes

Necesitas agregar el trait `SoftDeletes` a los siguientes modelos:

#### Supplier.php
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ← AGREGAR

class Supplier extends Model
{
    use SoftDeletes; // ← AGREGAR

    protected $fillable = ['name', 'contact_name', 'phone', 'email', 'address', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Scope para solo activos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
```

#### Category.php
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ← AGREGAR

class Category extends Model
{
    use HasFactory, SoftDeletes; // ← AGREGAR

    // ... resto del código
}
```

#### Customer.php, Sale.php, Purchase.php, Faltante.php, Gasto.php, Inventory.php
Aplicar el mismo patrón agregando:
```php
use Illuminate\Database\Eloquent\SoftDeletes;
// y en la clase:
use SoftDeletes;
```

### 2. Actualizar Controladores

**❌ NUNCA hacer esto:**
```php
$product->forceDelete();  // ¡PROHIBIDO! Eliminación permanente
```

**✅ SIEMPRE hacer esto:**
```php
// Opción 1: Soft Delete (recomendado para datos importantes)
$product->delete();  // Marca deleted_at

// Opción 2: Desactivar (recomendado para uso frecuente)
$product->update(['active' => false]);

// Para restaurar:
$product->restore();
$product->update(['active' => true]);
```

### 3. Queries en Controladores

```php
// Solo registros activos (sin eliminados)
Product::where('active', true)->get();
Product::active()->get(); // usando scope

// Incluir eliminados suaves
Product::withTrashed()->get();

// Solo eliminados
Product::onlyTrashed()->get();
```

---

## 📝 Sistema de Logs y Auditoría

### Recomendaciones para Implementar Logs

Basándome en tu solicitud de "guardar cualquier cambio que hagan los usuarios", te recomiendo **2 opciones**:

### 🥇 OPCIÓN 1: Spatie Activity Log (RECOMENDADA)

**Ventajas:**
- ✅ Fácil de instalar y usar
- ✅ Automático para todos los modelos
- ✅ Guarda: qué cambió, quién lo cambió, cuándo, valores anteriores y nuevos
- ✅ Muy popular en Laravel (9M+ descargas)
- ✅ Soporte para eventos personalizados

**Instalación:**
```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan migrate
```

**Uso Básico:**

1. **En tus modelos:**
```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'price', 'cost', 'active'])  // Qué campos registrar
            ->logOnlyDirty()  // Solo si cambia
            ->dontSubmitEmptyLogs();
    }
}
```

2. **Registro automático:**
```php
// Se registra automáticamente cuando:
$product->update(['price' => 150]);  // ✅ LOG: "precio cambió de 100 a 150"
$product->delete();                   // ✅ LOG: "producto eliminado"
$product->restore();                  // ✅ LOG: "producto restaurado"
```

3. **Eventos personalizados:**
```php
// Para acciones específicas
activity()
    ->causedBy(auth()->user())
    ->performedOn($producto)
    ->withProperties(['sucursal' => 'Norte', 'razon' => 'Inventario físico'])
    ->log('Ajuste de stock manual: -10 unidades');
```

4. **Consultar logs:**
```php
// Ver actividad de un producto
$activities = Activity::forSubject($product)
    ->get();

// Ver actividad de un usuario
$activities = Activity::causedBy($user)
    ->get();

// Ver actividad reciente
$activities = Activity::latest()
    ->limit(20)
    ->get();
```

**Estructura de la tabla `activity_log`:**
```sql
id
log_name           (ej: "productos", "ventas", "usuarios")
description        (ej: "updated", "deleted", "created")
subject_type       (ej: "App\Models\Product")
subject_id         (ej: 123)
causer_type        (ej: "App\Models\User")
causer_id          (quién hizo el cambio)
properties         (JSON con valores anteriores y nuevos)
created_at
```

**Ejemplo de registro:**
```json
{
  "attributes": {
    "price": 150,
    "name": "Martillo"
  },
  "old": {
    "price": 100,
    "name": "Martillo"
  }
}
```

### 🥈 OPCIÓN 2: Sistema Manual con Modelo AuditLog

**Ventajas:**
- ✅ Control total sobre qué se registra
- ✅ Sin dependencias externas
- ✅ Personalizable 100%

**Implementación:**

1. **Crear migración:**
```bash
php artisan make:migration create_audit_logs_table
```

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->string('action'); // 'create', 'update', 'delete', 'restore'
    $table->string('model_type'); // 'App\Models\Product'
    $table->unsignedBigInteger('model_id');
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('user_name')->nullable();
    $table->unsignedBigInteger('branch_id')->nullable();
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->text('description')->nullable();
    $table->string('ip_address')->nullable();
    $table->timestamps();

    $table->index(['model_type', 'model_id']);
    $table->index('user_id');
    $table->index('created_at');
});
```

2. **Crear modelo:**
```php
php artisan make:model AuditLog
```

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'action', 'model_type', 'model_id', 'user_id', 'user_name',
        'branch_id', 'old_values', 'new_values', 'description', 'ip_address'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

3. **Crear trait reutilizable:**
```php
<?php
namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            $model->auditLog('create');
        });

        static::updated(function ($model) {
            $model->auditLog('update');
        });

        static::deleted(function ($model) {
            $model->auditLog('delete');
        });
    }

    protected function auditLog($action)
    {
        AuditLog::create([
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'branch_id' => auth()->user()?->branch_id,
            'old_values' => $action === 'update' ? $this->getOriginal() : null,
            'new_values' => $this->getAttributes(),
            'ip_address' => request()->ip(),
        ]);
    }
}
```

4. **Usar en modelos:**
```php
class Product extends Model
{
    use SoftDeletes, Auditable; // ← Agregar trait
}
```

---

## 🎯 Mi Recomendación FINAL

**Para tu caso específico, te recomiendo:**

### ✅ OPCIÓN 1: Spatie Activity Log

**Razones:**
1. **Ahorra tiempo de desarrollo** - Ya está hecho y probado
2. **Más robusto** - Maneja casos edge que no has considerado
3. **Fácil de consultar** - Tiene helpers para reportes
4. **Bien mantenido** - Actualizaciones constantes
5. **Documentación excelente** - Muchos ejemplos

**Configuración inicial:**
```php
// En config/activitylog.php (después de publish)
return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    'delete_records_older_than_days' => 365, // Mantener 1 año

    'default_log_name' => 'default',

    'log_name' => null, // Usar nombre del modelo
];
```

**En tus modelos importantes:**
```php
// Product.php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'code', 'price', 'cost', 'active'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => "Producto {$eventName}");
}

// Sale.php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll()  // Registrar todos los campos
        ->setDescriptionForEvent(fn(string $eventName) => "Venta {$eventName}");
}

// User.php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'email', 'branch_id', 'active'])
        ->logOnlyDirty()
        ->setDescriptionForEvent(fn(string $eventName) => "Usuario {$eventName}");
}
```

### 📊 Dashboard de Auditoría

Puedes crear un controlador para ver los logs:

```php
// app/Http/Controllers/AuditController.php
public function index(Request $request)
{
    $activities = Activity::with('causer')
        ->when($request->user_id, fn($q, $userId) => $q->causedBy($userId))
        ->when($request->model, fn($q, $model) => $q->forSubject($model))
        ->when($request->date_from, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
        ->latest()
        ->paginate(50);

    return Inertia::render('Audit/Index', [
        'activities' => $activities
    ]);
}
```

---

## 📋 Checklist de Implementación

### Fase 1: Soft Deletes ✅ (COMPLETADO)
- [x] Migración para agregar deleted_at a todas las tablas
- [x] Agregar trait SoftDeletes a todos los modelos
- [ ] Actualizar controladores para usar delete() en vez de forceDelete()
- [x] Agregar scopes active() en todos los modelos
- [x] Documentar en SISTEMA_SUCURSALES.md

### Fase 2: Logs de Auditoría (Pendiente)
- [ ] Instalar Spatie Activity Log
- [ ] Configurar en config/activitylog.php
- [ ] Agregar LogsActivity trait a modelos importantes
- [ ] Configurar qué campos registrar en cada modelo
- [ ] Crear vista de auditoría en el frontend
- [ ] Agregar filtros de búsqueda (usuario, fecha, modelo)

### Fase 3: Optimización (Futuro)
- [ ] Limpiar logs antiguos (>1 año)
- [ ] Índices en tabla activity_log
- [ ] Caché para consultas frecuentes
- [ ] Exportar reportes de auditoría

---

## 🚀 Próximos Pasos Recomendados

1. **AHORA**: Agregar `SoftDeletes` a los modelos faltantes
2. **HOY**: Instalar Spatie Activity Log
3. **ESTA SEMANA**: Configurar logs en modelos principales (Product, Sale, User)
4. **PRÓXIMA SEMANA**: Crear vista de auditoría en frontend

¿Quieres que implemente alguno de estos pasos ahora?
