# Guía Rápida: Cómo Deshabilitar Ventas Libres y Faltantes Manuales

## 📍 Ubicación del Panel de Configuración

El panel de configuración está disponible en el **menú de navegación principal**, pero **SOLO es visible para el usuario Root (role_id = 1)**.

### Pasos para acceder:

```
1. Inicia sesión con un usuario que tenga role_id = 1 (Root/Administrador)
2. En el menú superior, verás: Dashboard | Ventas | Proveedores | Productos | Inventario | Faltantes | Usuarios | Configuración
3. Haz clic en "Configuración" (último item del menú)
4. Se abrirá el panel de configuración del sistema
```

## 🎛️ Controles Disponibles

### 1. Habilitar Ventas Libres
- **Toggle Switch** (ON/OFF)
- **Cuando está ON (verde):** El botón "Nueva Venta Libre" aparece en el módulo de Ventas
- **Cuando está OFF (gris):** El botón desaparece, solo queda "Venta de Catálogo"
- **Uso:** Desactiva esto cuando ya tengas todo tu inventario cargado

### 2. Habilitar Faltantes Manuales
- **Toggle Switch** (ON/OFF)
- **Cuando está ON (verde):** El botón "Agregar Faltante" aparece en el módulo de Faltantes
- **Cuando está OFF (gris):** El botón desaparece, solo se pueden generar faltantes automáticamente
- **Uso:** Desactiva esto cuando prefieras que los faltantes se generen solo desde stock bajo

### 3. Umbral de Stock Bajo
- **Input numérico** (valor por defecto: 5)
- Define cuántas unidades considera el sistema como "stock bajo"
- Afecta las estadísticas y la generación automática de faltantes

### 4. Generar Faltantes Automáticamente
- **Botón azul** "Generar Faltantes desde Stock Bajo"
- Al hacer clic, el sistema:
  - Busca todos los productos con stock ≤ umbral configurado
  - Crea faltantes automáticamente para productos sin faltantes pendientes
  - Calcula cantidad sugerida basándose en min_stock del producto

## ⚠️ Problema Común: No veo el link "Configuración"

Si NO ves el link "Configuración" en tu menú, es porque **tu usuario NO tiene asignado el role_id = 1**.

### Solución: Asignar rol Root a tu usuario

Tienes dos opciones:

#### Opción A: Desde la interfaz (módulo Usuarios)
```
1. Ve a "Usuarios" en el menú
2. Busca tu usuario
3. Asígnale el rol "Root" o "Administrador" (el que tenga id = 1)
```

#### Opción B: Desde la base de datos (phpMyAdmin o similar)
```sql
-- 1. Verificar qué usuario tienes actualmente
SELECT * FROM users WHERE email = 'tu_email@ejemplo.com';

-- 2. Verificar qué roles existen
SELECT * FROM roles;

-- 3. Asignar rol root (id = 1) a tu usuario
INSERT INTO role_user (role_id, user_id)
VALUES (1, TU_USER_ID);

-- Ejemplo si tu user_id es 1:
INSERT INTO role_user (role_id, user_id) VALUES (1, 1);
```

## 🔍 Verificar si tienes acceso Root

Para verificar si tu usuario actual tiene rol root, puedes revisar en la base de datos:

```sql
-- Reemplaza 1 con tu user_id
SELECT u.name, u.email, r.name as rol_name, r.id as rol_id
FROM users u
JOIN role_user ru ON u.id = ru.user_id
JOIN roles r ON ru.role_id = r.id
WHERE u.id = 1;
```

Si el resultado muestra `rol_id = 1`, entonces SÍ tienes acceso y deberías ver el link "Configuración".

## 📸 Vista del Panel de Configuración

El panel tiene:

```
┌─────────────────────────────────────────────────────┐
│  ⚠️ CONFIGURACIÓN DE SISTEMA                        │
│  [Banner amarillo explicando funciones temporales]  │
└─────────────────────────────────────────────────────┘

┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│ Stock Bajo   │  │  Agotados    │  │  Faltantes   │
│     15       │  │      3       │  │  Pendientes  │
└──────────────┘  └──────────────┘  └──────────────┘

┌─────────────────────────────────────────────────────┐
│ Configuración de Funcionalidades                    │
│                                                      │
│ ○ Habilitar Ventas Libres              [Toggle ON]  │
│   Permite registrar ventas sin productos...         │
│                                                      │
│ ○ Habilitar Faltantes Manuales         [Toggle ON]  │
│   Permite agregar faltantes manualmente...          │
│                                                      │
│ ○ Umbral de Stock Bajo                 [5      ]    │
│   Cantidad mínima para considerar stock bajo        │
│                                                      │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ Generar Faltantes Automáticamente                   │
│                                                      │
│ [Botón: Generar Faltantes desde Stock Bajo]         │
└─────────────────────────────────────────────────────┘
```

## 🎯 Flujo de Trabajo Recomendado

### Fase 1: Inicio del sistema (AHORA)
```
✅ Habilitar Ventas Libres = ON
✅ Habilitar Faltantes Manuales = ON
✅ Umbral Stock Bajo = 5
```
- Máxima flexibilidad para adaptar el sistema
- Puedes vender productos aún no catalogados
- Puedes agregar faltantes rápidamente

### Fase 2: Sistema funcionando normalmente (DESPUÉS)
```
❌ Habilitar Ventas Libres = OFF
❌ Habilitar Faltantes Manuales = OFF
✅ Umbral Stock Bajo = ajustado según experiencia
```
- Solo ventas de catálogo (con control de inventario)
- Faltantes generados automáticamente
- Sistema completamente controlado

## 💡 Notas Importantes

1. **Los cambios son instantáneos**: Al cambiar un toggle, recarga la página de Ventas o Faltantes y verás el cambio
2. **No afecta datos existentes**: Desactivar ventas libres NO borra las ventas libres anteriores
3. **Reversible**: Puedes activar/desactivar cuando quieras sin perder información
4. **Solo Root**: Solo el usuario con role_id = 1 puede acceder a esta configuración

## ❓ Preguntas Frecuentes

**P: ¿Puedo tener ambos desactivados?**
R: Sí, puedes tener ambos OFF si quieres un sistema completamente automático.

**P: ¿Qué pasa con las ventas libres existentes si desactivo la opción?**
R: Nada, se mantienen en el historial. Solo no podrás crear NUEVAS ventas libres.

**P: ¿Puedo ver este panel desde otro usuario?**
R: No, solo el usuario Root puede acceder. Es una medida de seguridad.

**P: ¿Puedo cambiar el umbral de stock bajo en cualquier momento?**
R: Sí, y afectará inmediatamente las estadísticas y futuras generaciones automáticas.

---

**¿Necesitas ayuda?** Verifica primero que tu usuario tenga role_id = 1 en la tabla role_user.
