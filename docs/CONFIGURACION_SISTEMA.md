# Configuración del Sistema - Ferretería TOTORO

## Descripción General

Se ha implementado un sistema de configuración que permite al usuario Root (role_id = 1) controlar funcionalidades temporales del sistema durante la fase de adaptación.

## Características Implementadas

### 1. **Configuraciones Disponibles**

#### a) Ventas Libres (`enable_ventas_libres`)
- **Tipo:** Boolean
- **Por defecto:** Habilitado (1)
- **Descripción:** Permite habilitar/deshabilitar la opción de ventas libres (productos no registrados en inventario)
- **Ubicación:** Módulo de Ventas
- **Efecto:** Cuando está deshabilitada, el botón "Nueva Venta Libre" no aparece en la interfaz de ventas

#### b) Faltantes Manuales (`enable_faltantes_manual`)
- **Tipo:** Boolean
- **Por defecto:** Habilitado (1)
- **Descripción:** Permite habilitar/deshabilitar el registro manual de productos faltantes
- **Ubicación:** Módulo de Faltantes
- **Efecto:** Cuando está deshabilitada, el botón "Agregar Faltante" no aparece en la interfaz de faltantes

#### c) Umbral de Stock Bajo (`stock_bajo_threshold`)
- **Tipo:** Integer
- **Por defecto:** 5 unidades
- **Descripción:** Define la cantidad mínima de stock para considerar un producto como "stock bajo"
- **Efecto:** Afecta las estadísticas del dashboard y la generación automática de faltantes

### 2. **Generación Automática de Faltantes**

El sistema incluye una funcionalidad para generar automáticamente faltantes basándose en productos con stock bajo:

- Busca productos con stock menor o igual al threshold configurado
- Verifica que no exista ya un faltante pendiente para el producto
- Calcula cantidad sugerida: `max(min_stock - stock_actual, 10)`
- Crea faltantes automáticamente con nota descriptiva

## Archivos Creados/Modificados

### Archivos Nuevos

1. **Migration:** `database/migrations/2025_08_14_000019_create_system_settings_table.php`
   - Crea tabla `system_settings` con estructura clave-valor
   - Inserta configuraciones por defecto

2. **Modelo:** `app/Models/SystemSetting.php`
   - Métodos estáticos para acceder a configuraciones:
     - `get($key, $default)` - Obtener valor con casting automático
     - `set($key, $value)` - Establecer valor
     - `ventasLibresHabilitadas()` - Helper para ventas libres
     - `faltantesManualesHabilitados()` - Helper para faltantes manuales
     - `stockBajoThreshold()` - Helper para threshold de stock

3. **Controlador:** `app/Http/Controllers/SettingsController.php`
   - `index()` - Muestra página de configuración (solo root)
   - `update()` - Actualiza una configuración
   - `generarFaltantesAutomatico()` - Genera faltantes desde stock bajo
   - `isRootUser()` - Verifica permisos de root

4. **Vista:** `resources/js/Pages/Settings/Index.vue`
   - Interfaz completa de configuración
   - Estadísticas del sistema
   - Toggle switches para configuraciones boolean
   - Input numérico para threshold
   - Banner de advertencia explicativo

### Archivos Modificados

1. **routes/web.php** (líneas 76-79)
   - Rutas para configuración del sistema
   ```php
   Route::get('/settings', [SettingsController::class, 'index']);
   Route::post('/settings/update', [SettingsController::class, 'update']);
   Route::post('/settings/generar-faltantes', [SettingsController::class, 'generarFaltantesAutomatico']);
   ```

2. **app/Http/Controllers/VentaController.php**
   - Importa `SystemSetting`
   - Pasa `ventasLibresHabilitadas` a la vista

3. **resources/js/Pages/Ventas/Index.vue**
   - Agrega prop `ventasLibresHabilitadas`
   - Condiciona botón "Nueva Venta Libre" con `v-if="ventasLibresHabilitadas"`

4. **app/Http/Controllers/FaltanteController.php**
   - Importa `SystemSetting`
   - Pasa `faltantesManualesHabilitados` a la vista

5. **resources/js/Pages/Faltantes/Index.vue**
   - Agrega prop `faltantesManualesHabilitados`
   - Condiciona botón "Agregar Faltante" con `v-if="faltantesManualesHabilitados"`

6. **resources/js/Layouts/AuthenticatedLayout.vue**
   - Agrega computed `isRoot` para verificar role_id = 1
   - Agrega link "Configuración" visible solo para root
   - Disponible en navegación desktop y móvil

## Acceso a Configuración

### Permisos
- **Solo** usuarios con `role_id = 1` pueden acceder
- Intento de acceso no autorizado retorna error 403

### Navegación
El link "Configuración" aparece en el menú principal después de "Usuarios", pero **solo para usuarios root**.

## Flujo de Uso Recomendado

### Fase 1: Adaptación Inicial (Ahora)
```
✅ enable_ventas_libres = true
✅ enable_faltantes_manual = true
✅ stock_bajo_threshold = 5
```
- Permite máxima flexibilidad durante la carga inicial de datos
- Ventas libres para productos aún no catalogados
- Faltantes manuales para registros rápidos

### Fase 2: Sistema Estabilizado (Futuro)
```
❌ enable_ventas_libres = false
❌ enable_faltantes_manual = false
✅ stock_bajo_threshold = ajustado según necesidad
```
- Solo ventas de catálogo (con control de inventario)
- Faltantes generados automáticamente desde stock bajo
- Sistema completamente integrado

## Comandos de Migración

Para aplicar la nueva tabla de configuración:

```bash
php artisan migrate
```

Esto creará la tabla `system_settings` con las 3 configuraciones por defecto.

## Estructura de Tabla

```sql
CREATE TABLE system_settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    type VARCHAR(255) DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Verificación de Implementación

### Checklist
- [x] Migración creada con configuraciones por defecto
- [x] Modelo SystemSetting con helpers
- [x] Controlador con verificación de permisos root
- [x] Vista de configuración con todas las opciones
- [x] Rutas protegidas por autenticación
- [x] Aplicación de settings en VentaController
- [x] Aplicación de settings en FaltanteController
- [x] Renderizado condicional en Ventas/Index.vue
- [x] Renderizado condicional en Faltantes/Index.vue
- [x] Link de configuración solo visible para root
- [x] Generación automática de faltantes funcional

## Capturas de Funcionalidad

### Panel de Configuración
- Banner de advertencia amarillo explicando el propósito temporal
- 3 tarjetas de estadísticas: Stock Bajo, Agotados, Faltantes Pendientes
- Toggle switches con diseño moderno para configuraciones boolean
- Input numérico para threshold de stock
- Botón para generar faltantes automáticamente

### Comportamiento Condicional
- **Ventas:** Botón "Nueva Venta Libre" solo visible si habilitado
- **Faltantes:** Botón "Agregar Faltante" solo visible si habilitado
- **Navegación:** Link "Configuración" solo visible para usuario root

## Notas Importantes

1. **Seguridad:** Todas las rutas de settings verifican `isRootUser()` antes de permitir acceso
2. **Flexibilidad:** Las configuraciones se pueden activar/desactivar en cualquier momento sin afectar datos existentes
3. **Escalabilidad:** Fácil agregar nuevas configuraciones siguiendo el mismo patrón
4. **UX:** Los cambios en configuración toman efecto inmediatamente al recargar la página

## Soporte y Mantenimiento

Para agregar nuevas configuraciones:

1. Agregar en migración (si es configuración permanente)
2. Agregar método helper en `SystemSetting.php`
3. Agregar control en vista `Settings/Index.vue`
4. Aplicar lógica condicional donde sea necesario

---

**Autor:** Claude Code
**Fecha:** 2025-08-14
**Versión:** 1.0
