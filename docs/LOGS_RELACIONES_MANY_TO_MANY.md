# 🔗 Logs para Relaciones Many-to-Many

## 📋 Problema Resuelto

Cuando se modifican relaciones **many-to-many** (como agregar categorías a un producto), los cambios solo afectan la **tabla pivot** (`category_product`), no el modelo `Product` directamente. Por lo tanto, **Spatie Activity Log NO registra estos cambios automáticamente**.

## ✅ Solución Implementada

Se agregaron **logs manuales** en todos los métodos del `ProductController` que modifican la relación de categorías.

---

## 📝 Métodos Actualizados

### 1. **store()** - Crear Producto con Categorías

**Ubicación**: [app/Http/Controllers/ProductController.php:146-162](app/Http/Controllers/ProductController.php#L146-L162)

**Qué registra:**
- Categorías asignadas al crear el producto

**Ejemplo de log:**
```
👤 Usuario: Juan Pérez
🎯 Acción: Categorías asignadas al crear producto: Herramientas, Ferretería
📦 Producto: Martillo (ID: 123)
📅 hace 2 minutos
```

**Propiedades guardadas:**
```json
{
  "category_ids": [1, 2],
  "category_names": ["Herramientas", "Ferretería"]
}
```

---

### 2. **update()** - Actualizar Producto y Categorías

**Ubicación**: [app/Http/Controllers/ProductController.php:189-222](app/Http/Controllers/ProductController.php#L189-L222)

**Qué registra:**
- Categorías agregadas
- Categorías removidas

**Ejemplo de log:**
```
👤 Usuario: Juan Pérez
🎯 Acción: Categorías modificadas: Agregadas: Pintura | Removidas: Herramientas
📦 Producto: Martillo (ID: 123)
📅 hace 5 minutos
```

**Propiedades guardadas:**
```json
{
  "old_category_ids": [1, 2],
  "new_category_ids": [2, 3],
  "added": ["Pintura"],
  "removed": ["Herramientas"]
}
```

---

### 3. **syncCategories()** - Sincronizar Categorías (API)

**Ubicación**: [app/Http/Controllers/ProductController.php:322-370](app/Http/Controllers/ProductController.php#L322-L370)

**Qué registra:**
- Todas las categorías que cambiaron en la sincronización

**Ejemplo de log:**
```
👤 Usuario: María García
🎯 Acción: Categorías sincronizadas: Agregadas: Electricidad, Plomería | Removidas: Jardinería
📦 Producto: Destornillador (ID: 456)
📅 hace 1 hora
```

**Propiedades guardadas:**
```json
{
  "old_category_ids": [5],
  "new_category_ids": [6, 7],
  "added": ["Electricidad", "Plomería"],
  "removed": ["Jardinería"]
}
```

---

### 4. **attachCategory()** - Agregar UNA Categoría

**Ubicación**: [app/Http/Controllers/ProductController.php:375-407](app/Http/Controllers/ProductController.php#L375-L407)

**Qué registra:**
- La categoría que se agregó

**Ejemplo de log:**
```
👤 Usuario: Pedro López
🎯 Acción: Categoría agregada: Construcción
📦 Producto: Cemento (ID: 789)
📅 hace 30 minutos
```

**Propiedades guardadas:**
```json
{
  "category_id": 8,
  "category_name": "Construcción"
}
```

---

### 5. **detachCategory()** - Remover UNA Categoría

**Ubicación**: [app/Http/Controllers/ProductController.php:412-437](app/Http/Controllers/ProductController.php#L412-L437)

**Qué registra:**
- La categoría que se removió

**Ejemplo de log:**
```
👤 Usuario: Ana Martínez
🎯 Acción: Categoría removida: Descontinuados
📦 Producto: Taladro (ID: 321)
📅 hace 15 minutos
```

**Propiedades guardadas:**
```json
{
  "category_id": 9,
  "category_name": "Descontinuados"
}
```

---

## 🎯 Cómo Funciona

### Antes (Sin logs)
```php
// Solo se modificaba la relación, sin registro
$product->categories()->attach($categoryId);
```

### Ahora (Con logs)
```php
// 1. Se modifica la relación
$product->categories()->attach($validated['category_id']);

// 2. Se registra el cambio manualmente
$category = \App\Models\Category::find($validated['category_id']);
activity()
    ->performedOn($product)           // En qué producto
    ->causedBy(auth()->user())        // Quién lo hizo
    ->withProperties([                // Datos adicionales
        'category_id' => $validated['category_id'],
        'category_name' => $category->name,
    ])
    ->log('Categoría agregada: ' . $category->name);  // Descripción
```

---

## 📊 Visualización en el Dashboard

Cuando accedas a `/auditoria`, verás estos registros como:

### Formato en la Tabla:
| Usuario | Acción | Modelo | Cambios | Fecha |
|---------|--------|--------|---------|-------|
| Juan Pérez | Log Manual | Producto (ID: 123) | Categorías modificadas: Agregadas: Pintura \| Removidas: Herramientas | 2024-12-20 15:30 |

### Detalles del Log:
```json
{
  "description": "Categorías modificadas: Agregadas: Pintura | Removidas: Herramientas",
  "causer": "Juan Pérez",
  "subject": "Producto #123 - Martillo",
  "properties": {
    "old_category_ids": [1, 2],
    "new_category_ids": [2, 3],
    "added": ["Pintura"],
    "removed": ["Herramientas"]
  }
}
```

---

## 🔍 Filtros Disponibles

En el dashboard de auditoría puedes filtrar:
- ✅ Por usuario que hizo el cambio
- ✅ Por producto específico
- ✅ Por rango de fechas
- ✅ Por tipo de modelo (selecciona "Productos")

---

## 💡 Aplicar el Mismo Patrón a Otras Relaciones

Si tienes otras relaciones many-to-many que quieras registrar, usa el mismo patrón:

### Ejemplo para Proveedores de Productos:

```php
// En ProductController@attachSupplier
public function attachSupplier(Request $request, Product $producto)
{
    // ... validación ...

    // Agregar proveedor
    $producto->suppliers()->attach($validated['supplier_id'], [
        'cost' => $validated['cost'],
        'is_preferred' => $validated['is_preferred'] ?? false,
    ]);

    // Registrar en activity log
    $supplier = Supplier::find($validated['supplier_id']);
    activity()
        ->performedOn($producto)
        ->causedBy(auth()->user())
        ->withProperties([
            'supplier_id' => $validated['supplier_id'],
            'supplier_name' => $supplier->name,
            'cost' => $validated['cost'],
            'is_preferred' => $validated['is_preferred'] ?? false,
        ])
        ->log('Proveedor agregado: ' . $supplier->name . ' (Costo: $' . $validated['cost'] . ')');

    return response()->json(['message' => 'Proveedor agregado exitosamente']);
}
```

---

## ✅ Checklist de Implementación

Para agregar logs a cualquier relación many-to-many:

1. **Identificar el método** que modifica la relación (attach, detach, sync)
2. **Obtener datos anteriores** (si es necesario para comparar)
3. **Realizar la modificación** de la relación
4. **Obtener datos nuevos** después del cambio
5. **Crear el log manual** con `activity()`
6. **Incluir propiedades útiles** para auditoría
7. **Usar descripciones claras** en español

---

## 🎉 Resultado Final

Ahora **TODOS** los cambios en las categorías de productos están registrados:
- ✅ Al crear un producto con categorías
- ✅ Al actualizar categorías de un producto
- ✅ Al sincronizar categorías (reemplazar todas)
- ✅ Al agregar una categoría
- ✅ Al remover una categoría

Todo visible en el dashboard de **Auditoría** con filtros y búsqueda avanzada.

---

## 📚 Documentación Relacionada

- [IMPLEMENTACION_ACTIVITY_LOG_COMPLETA.md](IMPLEMENTACION_ACTIVITY_LOG_COMPLETA.md) - Implementación general
- [SOFT_DELETES_Y_LOGS.md](SOFT_DELETES_Y_LOGS.md) - Sistema de soft deletes

---

## 🚀 Próximos Pasos (Opcional)

Si necesitas registrar más acciones, puedes agregar logs para:
- 📦 Proveedores de productos (attach/detach suppliers)
- 🏢 Transferencias de inventario entre sucursales
- 💰 Cambios de precios masivos
- 📊 Ajustes de stock manuales

Todos siguiendo el mismo patrón de `activity()` explicado arriba.
