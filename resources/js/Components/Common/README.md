# Componentes Comunes Reutilizables

Esta carpeta contiene componentes Vue reutilizables para reducir la repetición de código en las vistas Index.

## Componentes Disponibles

### 1. StatsCard

Tarjeta de estadísticas con icono y gradiente de color.

**Props:**
- `title` (String, required): Título de la estadística
- `value` (String|Number, required): Valor a mostrar
- `icon` (String, required): Path SVG del icono
- `colorFrom` (String, default: 'blue'): Color del gradiente (blue, green, orange, purple, red)
- `colorTo` (String, default: 'cyan'): Color destino (opcional, se calcula automáticamente)

**Ejemplo de uso:**
```vue
<StatsCard
    title="Total Proveedores"
    :value="stats.total"
    icon="M17 20h5v-2a3 3 0 00-5.356-1.857..."
    color-from="blue"
/>
```

### 2. SearchBar

Barra de búsqueda con contador de resultados y slot para filtros adicionales.

**Props:**
- `modelValue` (String, required): Valor del input (usar con v-model)
- `placeholder` (String, default: 'Buscar...'): Texto placeholder
- `label` (String, default: 'Buscar:'): Etiqueta del campo
- `total` (Number, default: 0): Total de registros
- `filtered` (Number, default: 0): Registros filtrados
- `showCount` (Boolean, default: true): Mostrar contador

**Slots:**
- `filters`: Slot para agregar selectores de filtros adicionales

**Ejemplo de uso:**
```vue
<SearchBar
    v-model="busqueda"
    label="Buscar proveedor:"
    placeholder="Nombre, contacto o email..."
    :total="proveedores.length"
    :filtered="proveedoresFiltrados.length"
>
    <template #filters>
        <select v-model="filtroEstado">
            <option value="">Todos</option>
            <option value="activo">Activos</option>
        </select>
    </template>
</SearchBar>
```

### 3. PageHeader

Encabezado de página con título, descripción y botón de acción.

**Props:**
- `title` (String, required): Título de la página
- `description` (String, default: ''): Descripción opcional
- `buttonText` (String, default: 'Nuevo'): Texto del botón
- `showButton` (Boolean, default: true): Mostrar u ocultar botón
- `buttonColor` (String, default: 'blue'): Color del botón (blue, green, purple, orange, red)

**Events:**
- `@action`: Emitido al hacer clic en el botón principal

**Slots:**
- `extra-buttons`: Slot para botones adicionales antes del botón principal

**Ejemplo de uso:**
```vue
<PageHeader
    title="Gestión de Proveedores"
    description="Administra tus proveedores y sus contactos"
    button-text="Nuevo Proveedor"
    button-color="blue"
    @action="abrirModalNuevo"
>
    <template #extra-buttons>
        <Link :href="route('export')" class="btn">Exportar</Link>
    </template>
</PageHeader>
```

### 4. EmptyState

Estado vacío para tablas sin datos.

**Props:**
- `icon` (String, required): Path SVG del icono
- `title` (String, default: 'No hay datos'): Título del mensaje
- `description` (String, default: ''): Descripción opcional

**Ejemplo de uso:**
```vue
<EmptyState
    icon="M17 20h5v-2a3 3 0 00-5.356-1.857..."
    title="No hay proveedores"
    description="Agrega proveedores usando el botón de arriba"
/>
```

## Iconos SVG Comunes

Para facilitar el uso, aquí están algunos iconos SVG comúnmente utilizados:

```javascript
// Proveedores/Usuarios
const iconUsers = "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"

// Check/Éxito
const iconCheck = "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"

// Alerta/Advertencia
const iconWarning = "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"

// Productos/Caja
const iconBox = "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"

// Inventario/Paquetes
const iconPackages = "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"

// Dinero/Ventas
const iconMoney = "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
```

## Ejemplo Completo de Refactorización

**Antes:**
```vue
<template #header>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                Gestión de Proveedores
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Administra tus proveedores
            </p>
        </div>
        <button @click="abrirModalNuevo" class="bg-gradient-to-r...">
            <svg>...</svg>
            Nuevo Proveedor
        </button>
    </div>
</template>
```

**Después:**
```vue
<script setup>
import PageHeader from '@/Components/Common/PageHeader.vue';
</script>

<template #header>
    <PageHeader
        title="Gestión de Proveedores"
        description="Administra tus proveedores"
        button-text="Nuevo Proveedor"
        @action="abrirModalNuevo"
    />
</template>
```

## Beneficios

1. **Menos código repetitivo**: Reduce de ~40 líneas a ~6 líneas
2. **Consistencia visual**: Todos los headers, stats y búsquedas se ven iguales
3. **Mantenimiento fácil**: Cambios en un solo lugar se reflejan en todas las vistas
4. **Personalización simple**: Props claras y slots para casos especiales
5. **Mejor legibilidad**: El código es más semántico y fácil de entender

## Siguiente Paso

Puedes refactorizar cualquier vista Index siguiendo este patrón:

1. Importa los componentes necesarios
2. Reemplaza el PageHeader
3. Reemplaza las StatsCards
4. Reemplaza el SearchBar
5. Reemplaza el EmptyState

Consulta `Proveedores/Index.vue` como ejemplo de referencia.
