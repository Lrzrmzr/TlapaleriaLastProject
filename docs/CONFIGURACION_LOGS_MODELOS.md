# Configuración de Activity Log en Modelos

## 📋 Modelos Principales a Configurar

Después de instalar Spatie Activity Log, configuraremos el trait `LogsActivity` en estos modelos:

### 1. Product (Productos)
**Campos importantes a registrar:**
- `name` - Nombre del producto
- `barcode` - Código de barras
- `price` - Precio de venta
- `cost` - Costo
- `active` - Estado activo/inactivo

**Eventos a registrar:**
- created - Cuando se crea un producto
- updated - Cuando se actualiza un producto
- deleted - Cuando se elimina (soft delete)
- restored - Cuando se restaura

---

### 2. Sale (Ventas)
**Campos importantes a registrar:**
- `customer_id` - Cliente
- `branch_id` - Sucursal
- `total` - Total de la venta
- `status` - Estado de la venta

**Eventos a registrar:**
- created - Nueva venta
- updated - Modificación de venta
- deleted - Cancelación de venta

---

### 3. Purchase (Compras)
**Campos importantes a registrar:**
- `supplier_id` - Proveedor
- `branch_id` - Sucursal
- `total` - Total de la compra
- `status` - Estado de la compra

**Eventos a registrar:**
- created - Nueva compra
- updated - Modificación de compra
- deleted - Cancelación de compra

---

### 4. User (Usuarios)
**Campos importantes a registrar:**
- `name` - Nombre
- `email` - Email
- `branch_id` - Sucursal asignada
- `active` - Estado

**Eventos a registrar:**
- created - Nuevo usuario
- updated - Modificación de usuario
- deleted - Desactivación de usuario
- restored - Reactivación de usuario

---

### 5. BranchInventory (Inventario por Sucursal)
**Campos importantes a registrar:**
- `branch_id` - Sucursal
- `product_id` - Producto
- `stock` - Stock actual
- `min_stock` - Stock mínimo
- `max_stock` - Stock máximo

**Eventos a registrar:**
- created - Nuevo producto en inventario
- updated - Ajuste de inventario
- deleted - Eliminación de producto del inventario

---

### 6. Supplier (Proveedores)
**Campos importantes a registrar:**
- `name` - Nombre
- `contact_name` - Contacto
- `phone` - Teléfono
- `email` - Email
- `active` - Estado

---

### 7. Customer (Clientes)
**Campos importantes a registrar:**
- `name` - Nombre
- `phone` - Teléfono
- `email` - Email
- `active` - Estado

---

## 🎯 Configuración Estándar

Todos los modelos tendrán esta configuración base:

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ModelName extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['campo1', 'campo2', 'campo3']) // Campos específicos
            ->logOnlyDirty() // Solo registrar si cambia
            ->dontSubmitEmptyLogs() // No registrar si no hay cambios
            ->setDescriptionForEvent(fn(string $eventName) => "Nombre del modelo {$eventName}");
    }
}
```

## ⚙️ Opciones Avanzadas

### Registrar TODO (para modelos críticos como Sale, Purchase)
```php
return LogOptions::defaults()
    ->logAll() // Registra TODOS los campos
    ->logOnlyDirty()
    ->dontSubmitEmptyLogs();
```

### Registrar usuario que hizo el cambio
Automáticamente registra `auth()->user()` como el causante del cambio.

### Eventos personalizados
```php
// En controladores o servicios
activity()
    ->causedBy(auth()->user())
    ->performedOn($producto)
    ->withProperties(['sucursal' => 'Norte', 'razon' => 'Inventario físico'])
    ->log('Ajuste manual de inventario: -10 unidades');
```

## 📊 Estructura del Log

Cada registro en `activity_log` tendrá:

```json
{
  "log_name": "productos",
  "description": "updated",
  "subject_type": "App\\Models\\Product",
  "subject_id": 123,
  "causer_type": "App\\Models\\User",
  "causer_id": 1,
  "properties": {
    "attributes": {
      "price": 150.00,
      "name": "Martillo"
    },
    "old": {
      "price": 100.00,
      "name": "Martillo"
    }
  },
  "created_at": "2024-12-20 10:30:00"
}
```

## 🚀 Próximos Pasos

1. ✅ Instalar Spatie Activity Log (comandos en INSTALACION_SPATIE_ACTIVITYLOG.md)
2. ⏳ Aplicar trait a modelos principales
3. ⏳ Crear controlador de auditoría
4. ⏳ Crear vista para visualizar logs
5. ⏳ Probar el sistema
