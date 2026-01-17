# Sistema de Categorías para Productos

## Descripción

Sistema de categorización de productos con relación **many-to-many**. Un producto puede tener múltiples categorías y una categoría puede tener múltiples productos.

**Ejemplos de uso:**
- Un martillo puede estar en "Carpintería" y "Ferretería General"
- Un taladro puede estar en "Carpintería", "Electricidad" y "Ferretería General"
- Cemento puede estar en "Albañilería" y "Construcción"

---

## 📊 Estructura de Base de Datos

### Tabla `categories`

```sql
CREATE TABLE categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,           -- "Carpintería", "Plomería"
    slug VARCHAR(100) UNIQUE NOT NULL,           -- "carpinteria", "plomeria"
    description TEXT NULL,                       -- Descripción de la categoría
    icon VARCHAR(50) NULL,                       -- Emoji o clase de icono
    color VARCHAR(7) DEFAULT '#3B82F6',          -- Color en formato hex
    active BOOLEAN DEFAULT TRUE,                 -- Si está activa
    order INT DEFAULT 0,                         -- Orden de visualización
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabla Pivote `category_product`

```sql
CREATE TABLE category_product (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_product (category_id, product_id)
);
```

**Índices:**
- `category_id` - Para búsquedas por categoría
- `product_id` - Para búsquedas por producto
- `UNIQUE(category_id, product_id)` - Evita duplicados

---

## 🎨 Categorías Predefinidas

El sistema incluye 10 categorías iniciales con colores e íconos:

| Categoría | Slug | Icono | Color | Descripción |
|-----------|------|-------|-------|-------------|
| Carpintería | `carpinteria` | 🔨 | #8B4513 | Herramientas y materiales para carpintería |
| Albañilería | `albanileria` | 🧱 | #DC2626 | Materiales para construcción |
| Plomería | `plomeria` | 🚰 | #2563EB | Tuberías y conexiones |
| Electricidad | `electricidad` | ⚡ | #FBBF24 | Cables y material eléctrico |
| Pintura | `pintura` | 🎨 | #7C3AED | Pinturas, barnices y brochas |
| Jardinería | `jardineria` | 🌱 | #16A34A | Herramientas de jardín |
| Ferretería General | `ferreteria-general` | 🔧 | #64748B | Productos generales |
| Herrajes | `herrajes` | 🔩 | #475569 | Bisagras, cerraduras, manijas |
| Seguridad | `seguridad` | 🔒 | #EF4444 | Candados y equipos de seguridad |
| Limpieza | `limpieza` | 🧹 | #06B6D4 | Productos de limpieza |

---

## 🔧 Modelos

### Category Model

**[app/Models/Category.php](../app/Models/Category.php)**

```php
class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'color', 'active', 'order'
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    // Relación con productos
    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}
```

### Product Model (Actualizado)

**[app/Models/Product.php](../app/Models/Product.php)**

```php
class Product extends Model
{
    // Relación con categorías
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')
            ->withTimestamps();
    }
}
```

---

## 🌐 API Endpoints

### Categorías

#### Listar Categorías
```http
GET /api/admin/categories
Authorization: Bearer {token}

Query Parameters:
- active (boolean): Filtrar por activas
- search (string): Buscar por nombre
- with_products_count (boolean): Incluir conteo de productos
- all (boolean): Obtener todas sin paginación
- per_page (int): Items por página (default: 15)
```

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Carpintería",
      "slug": "carpinteria",
      "description": "Herramientas y materiales...",
      "icon": "🔨",
      "color": "#8B4513",
      "active": true,
      "order": 1,
      "products_count": 25
    }
  ],
  "meta": { ... },
  "links": { ... }
}
```

#### Categorías Activas (para selectores)
```http
GET /api/admin/categories-active
Authorization: Bearer {token}
```

**Respuesta:**
```json
[
  {
    "id": 1,
    "name": "Carpintería",
    "slug": "carpinteria",
    "icon": "🔨",
    "color": "#8B4513"
  }
]
```

#### Crear Categoría
```http
POST /api/admin/categories
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Nueva Categoría",
  "slug": "nueva-categoria",      // Opcional, se genera automáticamente
  "description": "Descripción...",
  "icon": "🛠️",
  "color": "#3B82F6",
  "active": true,
  "order": 0
}
```

#### Ver Categoría
```http
GET /api/admin/categories/{id}
Authorization: Bearer {token}
```

#### Actualizar Categoría
```http
PUT /api/admin/categories/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Nombre Actualizado",
  "active": false,
  "order": 5
}
```

#### Eliminar Categoría
```http
DELETE /api/admin/categories/{id}
Authorization: Bearer {token}
```

**Nota:** No se puede eliminar una categoría que tiene productos asociados.

#### Reordenar Categorías
```http
POST /api/admin/categories/reorder
Authorization: Bearer {token}
Content-Type: application/json

{
  "categories": [
    { "id": 1, "order": 0 },
    { "id": 2, "order": 1 },
    { "id": 3, "order": 2 }
  ]
}
```

---

### Gestión de Categorías de Productos

#### Obtener Categorías de un Producto
```http
GET /api/admin/products/{product_id}/categories
Authorization: Bearer {token}
```

**Respuesta:**
```json
[
  {
    "id": 1,
    "name": "Carpintería",
    "slug": "carpinteria",
    "icon": "🔨",
    "color": "#8B4513",
    "pivot": {
      "product_id": 5,
      "category_id": 1,
      "created_at": "2024-01-15 10:30:00"
    }
  }
]
```

#### Sincronizar Categorías (Reemplazar Todas)
```http
POST /api/admin/products/{product_id}/categories/sync
Authorization: Bearer {token}
Content-Type: application/json

{
  "category_ids": [1, 3, 5]
}
```

**Comportamiento:** Elimina todas las categorías previas y agrega solo las especificadas.

#### Agregar una Categoría
```http
POST /api/admin/products/{product_id}/categories
Authorization: Bearer {token}
Content-Type: application/json

{
  "category_id": 3
}
```

**Respuesta:**
```json
{
  "message": "Categoría agregada exitosamente",
  "categories": [ ... ]
}
```

#### Eliminar una Categoría
```http
DELETE /api/admin/products/{product_id}/categories/{category_id}
Authorization: Bearer {token}
```

---

## 💻 Uso en Código

### Crear Producto con Categorías

```php
$product = Product::create([
    'name' => 'Martillo',
    'description' => 'Martillo de carpintero',
    'price' => 150.00
]);

// Agregar categorías
$product->categories()->attach([1, 7]); // Carpintería + Ferretería General
```

### Actualizar Categorías

```php
// Sincronizar (reemplazar todas)
$product->categories()->sync([1, 3, 5]);

// Agregar sin quitar las existentes
$product->categories()->attach(4);

// Quitar una categoría específica
$product->categories()->detach(3);

// Verificar si tiene una categoría
if ($product->categories()->where('category_id', 1)->exists()) {
    // Tiene categoría de Carpintería
}
```

### Consultas con Eager Loading

```php
// Obtener productos con sus categorías
$products = Product::with('categories')->get();

foreach ($products as $product) {
    foreach ($product->categories as $category) {
        echo "{$product->name} - {$category->name}\n";
    }
}

// Filtrar productos por categoría
$carpenteriaProducts = Category::find(1)->products;

// Productos que tienen categoría específica
$products = Product::whereHas('categories', function($query) {
    $query->where('slug', 'carpinteria');
})->get();

// Productos con múltiples categorías
$products = Product::whereHas('categories', function($query) {
    $query->whereIn('slug', ['carpinteria', 'electricidad']);
})->get();
```

---

## 🎯 Casos de Uso Comunes

### 1. Mostrar Productos por Categoría

```php
$category = Category::where('slug', 'carpinteria')->first();
$products = $category->products()->with('inventory')->get();
```

### 2. Filtrar Productos por Múltiples Categorías

```php
$products = Product::whereHas('categories', function($query) use ($categoryIds) {
    $query->whereIn('categories.id', $categoryIds);
})->distinct()->get();
```

### 3. Productos con Todas las Categorías Especificadas

```php
$categoryIds = [1, 3, 5];
$products = Product::whereHas('categories', function($query) use ($categoryIds) {
    $query->whereIn('categories.id', $categoryIds);
}, '=', count($categoryIds))->get();
```

### 4. Categorías más Populares

```php
$popularCategories = Category::withCount('products')
    ->orderByDesc('products_count')
    ->limit(5)
    ->get();
```

---

## 🚀 Migraciones y Seeders

### Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará:
- Tabla `categories`
- Tabla pivote `category_product`

### Ejecutar Seeder

```bash
php artisan db:seed --class=CategorySeeder
```

Esto creará las 10 categorías predefinidas.

### Migración Completa

```bash
php artisan migrate:fresh --seed
```

---

## ✅ Validaciones

### Al Crear/Actualizar Categoría

- `name`: Requerido, máximo 100 caracteres, único
- `slug`: Opcional (se genera automáticamente), único
- `description`: Opcional, máximo 500 caracteres
- `icon`: Opcional, máximo 50 caracteres
- `color`: Opcional, formato hex (#RRGGBB)
- `active`: Booleano
- `order`: Entero, mínimo 0

### Al Asignar Categorías a Producto

- `category_ids`: Array requerido
- `category_ids.*`: Deben existir en la tabla categories

### Restricciones

- No se puede eliminar una categoría con productos asociados
- No se puede asignar la misma categoría dos veces a un producto (índice único)

---

## 🎨 Frontend (Ejemplo)

### Mostrar Categorías de un Producto

```vue
<template>
  <div class="flex gap-2">
    <span
      v-for="category in product.categories"
      :key="category.id"
      class="px-2 py-1 rounded text-white text-xs"
      :style="{ backgroundColor: category.color }"
    >
      {{ category.icon }} {{ category.name }}
    </span>
  </div>
</template>
```

### Selector de Categorías (Multi-select)

```vue
<template>
  <div>
    <label>Categorías:</label>
    <div class="flex flex-wrap gap-2">
      <label
        v-for="category in categories"
        :key="category.id"
        class="flex items-center gap-2 p-2 border rounded cursor-pointer"
        :class="{ 'bg-blue-100': selectedIds.includes(category.id) }"
      >
        <input
          type="checkbox"
          :value="category.id"
          v-model="selectedIds"
        >
        <span>{{ category.icon }} {{ category.name }}</span>
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const selectedIds = ref([]);
const categories = ref([]);

// Cargar categorías activas
const loadCategories = async () => {
  const response = await axios.get('/api/admin/categories-active');
  categories.value = response.data;
};

// Guardar categorías del producto
const saveCategories = async (productId) => {
  await axios.post(`/api/admin/products/${productId}/categories/sync`, {
    category_ids: selectedIds.value
  });
};
</script>
```

---

## 📝 Notas Importantes

1. **Slug Automático**: El slug se genera automáticamente basado en el nombre usando `Str::slug()`

2. **Eliminación en Cascada**: Si se elimina una categoría o producto, la relación en `category_product` se elimina automáticamente

3. **Índice Único**: No se puede asignar la misma categoría dos veces al mismo producto

4. **Soft Deletes**: Las categorías NO usan soft deletes. Si necesitas conservar historial, cambia a `active = false` en lugar de eliminar

5. **Ordenamiento**: Las categorías se ordenan por `order` y luego por `name` alfabéticamente

---

**Última actualización:** Diciembre 2025
