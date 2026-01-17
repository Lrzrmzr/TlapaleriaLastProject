# ✅ Implementación Completa de Spatie Activity Log

## 📋 Resumen de Implementación

Se ha implementado exitosamente el sistema de auditoría usando **Spatie Activity Log** para registrar todos los cambios que los usuarios hacen en el sistema.

---

## 🎯 Lo que se ha Completado

### ✅ 1. Instalación del Paquete
- [x] Instalado `spatie/laravel-activitylog` vía Composer
- [x] Publicadas las migraciones
- [x] Publicada la configuración
- [x] Ejecutadas las migraciones (tabla `activity_log` creada)

### ✅ 2. Configuración de Modelos con LogsActivity

Se configuró el trait `LogsActivity` en los siguientes modelos:

#### **Product** ([app/Models/Product.php](app/Models/Product.php))
```php
use LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'description', 'barcode', 'price', 'cost', 'supplier_id', 'active'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => "Producto {$eventName}");
}
```

#### **Sale** ([app/Models/Sale.php](app/Models/Sale.php))
```php
use LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll() // Registra TODOS los campos (crítico)
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => "Venta {$eventName}");
}
```

#### **Purchase** ([app/Models/Purchase.php](app/Models/Purchase.php))
```php
use LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll() // Registra TODOS los campos (crítico)
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => "Compra {$eventName}");
}
```

#### **User** ([app/Models/User.php](app/Models/User.php))
```php
use LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'email', 'branch_id', 'active'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => "Usuario {$eventName}");
}
```

#### **BranchInventory** ([app/Models/BranchInventory.php](app/Models/BranchInventory.php))
```php
use LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['branch_id', 'product_id', 'stock', 'min_stock', 'max_stock', 'cost', 'active'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => "Inventario {$eventName}");
}
```

#### **Supplier** ([app/Models/Supplier.php](app/Models/Supplier.php))
```php
use LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'contact_name', 'phone', 'email', 'address', 'active'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => "Proveedor {$eventName}");
}
```

#### **Customer** ([app/Models/Customer.php](app/Models/Customer.php))
```php
use LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'phone', 'email', 'address', 'active'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => "Cliente {$eventName}");
}
```

### ✅ 3. Controlador de Auditoría

**Archivo**: [app/Http/Controllers/AuditController.php](app/Http/Controllers/AuditController.php)

**Métodos implementados:**
- `index()` - Lista todos los logs con filtros avanzados
- `show($id)` - Muestra detalles de un log específico

**Filtros disponibles:**
- 🔍 **Por Usuario** - Ver cambios de un usuario específico
- 📦 **Por Modelo** - Filtrar por tipo de registro (Productos, Ventas, etc.)
- 🎯 **Por Acción** - Creado, Actualizado, Eliminado, Restaurado
- 📅 **Por Fecha** - Rango de fechas (desde - hasta)
- 🏢 **Por Sucursal** - Solo para administradores root

### ✅ 4. Rutas Configuradas

**Archivo**: [routes/web.php](routes/web.php)

```php
// Auditoría (Activity Logs)
Route::get('/auditoria', [AuditController::class, 'index'])->name('auditoria.index');
Route::get('/auditoria/{id}', [AuditController::class, 'show'])->name('auditoria.show');
```

### ✅ 5. Vista Vue Dashboard de Auditoría

**Archivo**: [resources/js/Pages/Auditoria/Index.vue](resources/js/Pages/Auditoria/Index.vue)

**Características:**
- 📊 **Panel de filtros** completo con todos los criterios
- 📋 **Tabla de logs** con información detallada:
  - Usuario que hizo el cambio
  - Acción realizada (con badges de colores)
  - Modelo afectado
  - Cambios específicos (valores anteriores → nuevos)
  - Fecha y hora del cambio
- 🎨 **Diseño responsive** con gradientes purple/morado
- 📄 **Paginación** de 50 registros por página
- 🔄 **Actualización en tiempo real** vía Inertia.js

### ✅ 6. Menú de Navegación Actualizado

Se agregó el enlace "Auditoría" en el menú principal del sistema:

**Archivo**: [resources/js/Layouts/AuthenticatedLayout.vue](resources/js/Layouts/AuthenticatedLayout.vue)

---

## 📊 Estructura de la Tabla `activity_log`

```sql
CREATE TABLE activity_log (
    id BIGINT PRIMARY KEY,
    log_name VARCHAR(255),              -- Ej: "productos", "ventas"
    description VARCHAR(255),            -- Ej: "created", "updated", "deleted"
    subject_type VARCHAR(255),           -- Ej: "App\Models\Product"
    subject_id BIGINT,                   -- ID del registro afectado
    causer_type VARCHAR(255),            -- Ej: "App\Models\User"
    causer_id BIGINT,                    -- ID del usuario que hizo el cambio
    properties JSON,                     -- Valores anteriores y nuevos
    batch_uuid VARCHAR(255),             -- UUID para agrupar cambios
    event VARCHAR(255),                  -- Evento que disparó el log
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🎯 ¿Qué se Registra Automáticamente?

### Eventos Registrados:
- ✅ **created** - Cuando se crea un nuevo registro
- ✅ **updated** - Cuando se actualiza un registro
- ✅ **deleted** - Cuando se elimina (soft delete)
- ✅ **restored** - Cuando se restaura un registro eliminado

### Información Capturada:
- 👤 **Usuario** que hizo el cambio (`auth()->user()`)
- 📦 **Modelo** y **ID** del registro afectado
- 🔄 **Valores anteriores** y **valores nuevos** de cada campo
- 📅 **Fecha y hora** exacta del cambio
- 🏢 **Sucursal** asociada (si aplica)

---

## 📝 Ejemplo de Uso

### Cuando un usuario actualiza un producto:

**Acción del usuario:**
```php
$producto->update([
    'name' => 'Martillo Nuevo',
    'price' => 150.00
]);
```

**Log generado automáticamente:**
```json
{
  "log_name": "default",
  "description": "updated",
  "subject_type": "App\\Models\\Product",
  "subject_id": 123,
  "causer_type": "App\\Models\\User",
  "causer_id": 1,
  "properties": {
    "attributes": {
      "name": "Martillo Nuevo",
      "price": 150.00
    },
    "old": {
      "name": "Martillo Viejo",
      "price": 100.00
    }
  }
}
```

**Se ve en el dashboard como:**
```
👤 Usuario: Juan Pérez
🎯 Acción: Actualizado
📦 Modelo: Producto (ID: 123)
🔄 Cambios:
   - name: Martillo Viejo → Martillo Nuevo
   - price: 100.00 → 150.00
📅 Fecha: 2024-12-20 15:30:00 (hace 2 horas)
```

---

## 🚀 Cómo Acceder al Dashboard de Auditoría

1. Inicia sesión en el sistema
2. Haz clic en **"Auditoría"** en el menú principal
3. Usa los filtros para buscar logs específicos:
   - Selecciona un usuario
   - Selecciona un tipo de registro
   - Define un rango de fechas
   - Selecciona una acción
4. Haz clic en **"Aplicar Filtros"**

---

## 🔧 Logs Personalizados (Opcional)

Si necesitas registrar eventos específicos manualmente, usa:

```php
// En cualquier controlador o servicio
activity()
    ->causedBy(auth()->user())
    ->performedOn($producto)
    ->withProperties([
        'sucursal' => 'Norte',
        'razon' => 'Ajuste por inventario físico',
        'cantidad_anterior' => 100,
        'cantidad_nueva' => 90,
    ])
    ->log('Ajuste manual de inventario: -10 unidades');
```

---

## 📈 Consultas Útiles

### Ver actividad de un producto específico:
```php
use Spatie\Activitylog\Models\Activity;

$activities = Activity::forSubject($producto)->get();
```

### Ver actividad de un usuario:
```php
$activities = Activity::causedBy($user)->get();
```

### Ver actividad reciente (últimos 20 registros):
```php
$activities = Activity::latest()->limit(20)->get();
```

---

## 🎨 Códigos de Color en el Dashboard

- 🟢 **Verde** - Creado (created)
- 🔵 **Azul** - Actualizado (updated)
- 🔴 **Rojo** - Eliminado (deleted)
- 🟣 **Morado** - Restaurado (restored)

---

## ⚙️ Configuración (Opcional)

El archivo de configuración se encuentra en:
**`config/activitylog.php`**

Configuraciones útiles:
```php
return [
    // Habilitar/deshabilitar logs
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    // Eliminar logs antiguos (días)
    'delete_records_older_than_days' => 365,

    // Nombre del log por defecto
    'default_log_name' => 'default',
];
```

---

## 🧹 Mantenimiento

### Limpiar logs antiguos (mayores a 1 año):

```php
// En un comando artisan o tarea programada
use Spatie\Activitylog\Models\Activity;

Activity::where('created_at', '<', now()->subYear())->delete();
```

### Ver estadísticas:
```php
// Total de logs
Activity::count();

// Logs del último mes
Activity::where('created_at', '>=', now()->subMonth())->count();

// Logs por usuario
Activity::where('causer_id', $userId)->count();
```

---

## ✅ Estado del Sistema

### Modelos con Activity Log:
- ✅ Product
- ✅ Sale
- ✅ Purchase
- ✅ User
- ✅ BranchInventory
- ✅ Supplier
- ✅ Customer

### Soft Deletes:
- ✅ Todos los modelos tienen SoftDeletes implementado
- ✅ NINGÚN dato se elimina permanentemente
- ✅ Todos los "deletes" son registrados en activity_log

---

## 🎉 ¡Sistema Completo y Funcional!

El sistema de auditoría está **100% funcional** y listo para usar. Cada vez que:
- Se cree un registro
- Se actualice un registro
- Se elimine un registro
- Se restaure un registro

El sistema **automáticamente** guardará:
- Quién lo hizo
- Qué cambió
- Cuándo lo hizo
- Valores anteriores y nuevos

Todo visible en el dashboard de **Auditoría** con filtros avanzados.

---

## 📞 Soporte

Para más información sobre Spatie Activity Log:
- [Documentación Oficial](https://spatie.be/docs/laravel-activitylog)
- [GitHub](https://github.com/spatie/laravel-activitylog)
