# Análisis SaaS — Sistema de Gestión para Ferreterías y Negocios de Retail

> Fecha de análisis: Abril 2026  
> Proyecto: Tlapaleria / TOTORO  
> Stack: Laravel 12 + Vue 3 + Inertia.js + Tailwind CSS

---

## 1. Estado actual del sistema

### Módulos implementados

| Módulo | Estado | Notas |
|--------|--------|-------|
| Autenticación (web + OAuth 2.0 móvil) | ✅ Completo | Laravel Passport |
| Multi-sucursal con roles | ✅ Completo | root / administrador / trabajador / cliente |
| Inventario por sucursal | ✅ Completo | Stock mínimo, máximo, alertas críticas |
| Productos y categorías | ✅ Completo | Multi-proveedor por producto |
| Proveedores | ✅ Completo | Códigos, costos por proveedor, proveedor preferido |
| Ventas (libre y catálogo) | ✅ Completo | Con utilidad calculada |
| Compras / entradas | ✅ Parcial | Sin flujo de orden de compra formal |
| Gastos operativos | ✅ Completo | Por sucursal y usuario |
| Faltantes / pedidos | ✅ Completo | TRUPER y GENERAL con confirmación |
| Auditoría (Activity Log) | ✅ Completo | Spatie Activity Log |
| API móvil (Android/Capacitor) | ✅ Completo | OAuth, endpoints separados |
| Dashboard con estadísticas | ✅ Completo | Ventas, utilidad, clientes, faltantes |
| **Cuentas por Pagar** | ✅ Implementado | Notas de proveedor + historial de pagos |
| **Cuentas por Cobrar** | ✅ Implementado | Ventas a crédito + historial de cobros |

---

## 2. Gaps críticos para convertirlo en SaaS vendible

### 2.1 Multi-tenancy (PRIORIDAD ALTA)

**Qué falta:** El sistema actual es monolítico. Todas las ferreterías compartirían la misma base de datos sin separación de datos.

**Solución recomendada:** Agregar `tenant_id` a todas las tablas principales, con middleware que inyecte el tenant en cada query (usando `Tenancy for Laravel` o `Stancl/Tenancy`).

**Impacto:** Sin esto, no es posible vender el sistema a múltiples empresas de forma segura.

---

### 2.2 Facturación electrónica / CFDI (PRIORIDAD ALTA — mercado México)

**Qué falta:**
- Emisión de facturas con timbrado SAT
- Series y folios fiscales
- Cancelaciones de CFDI
- RFC del cliente en el registro
- Complementos de pago (para ventas a crédito)

**Integración sugerida:** Facturama API o SW Sapien para el timbrado.

---

### 2.3 Punto de Venta (POS) completo (PRIORIDAD ALTA)

**Qué falta:**
- Interfaz táctil optimizada para tablet / pantalla de caja
- Cobro con múltiples formas de pago en una sola venta (efectivo + tarjeta)
- Corte de caja (apertura/cierre con diferencia de efectivo)
- Ticket imprimible (80mm) con logo y datos fiscales
- Descuentos por venta y por línea

---

### 2.4 Sistema de suscripciones y planes (PRIORIDAD ALTA)

**Qué falta:**
- Tabla `plans` (básico, estándar, premium) con límites:
  - N° de sucursales
  - N° de usuarios
  - N° de productos
  - Módulos habilitados (CxP, CxC, facturación, etc.)
- Tabla `subscriptions` con fecha de inicio/fin, status
- Pasarela de pago: Stripe (internacional) o Conekta (México)
- Bloqueo automático al expirar la suscripción

---

### 2.5 Reportes financieros (PRIORIDAD ALTA)

**Qué falta:**
- Estado de Resultados (ingresos - gastos - costo de ventas = utilidad)
- Flujo de efectivo
- Reporte de antigüedad de saldos (CxP y CxC vencidas por rango)
- Comparativo de ventas por período (día/semana/mes/año)
- Reporte de margen por producto y categoría
- Exportación a PDF y Excel

---

### 2.6 Gestión de clientes (CRM básico) (PRIORIDAD MEDIA)

**Qué falta:**
- Historial completo de compras por cliente (ventas + créditos)
- Límite de crédito configurable por cliente
- Estado de cuenta imprimible / enviable por email
- Clasificación de clientes (A, B, C por volumen)

---

### 2.7 Órdenes de compra formales (PRIORIDAD MEDIA)

**Qué falta:**
- Flujo completo: Solicitud → Aprobación → Envío a proveedor → Recepción parcial/total → Genera CxP automáticamente
- Comparativo de precios entre proveedores al crear la orden

---

### 2.8 Devoluciones (PRIORIDAD MEDIA)

**Qué falta:**
- Devoluciones de venta: genera nota de crédito a cliente y regresa stock
- Devoluciones a proveedor: genera nota de crédito de proveedor y reduce CxP

---

### 2.9 Alertas y notificaciones (PRIORIDAD MEDIA)

**Qué falta:**
- Email / WhatsApp al cliente cuando su cuenta está por vencer
- Notificación push al administrador por cuentas vencidas
- Alerta de stock crítico en tiempo real (WebSockets o polling)
- Recordatorio automático de pagos a proveedores

---

### 2.10 Exportación de datos y respaldos (PRIORIDAD MEDIA)

**Qué falta:**
- Exportar inventario a Excel
- PDF de estado de cuenta (CxP y CxC)
- Respaldo de datos por tenant en formato CSV
- Portabilidad de datos (para evitar vendor lock-in)

---

### 2.11 Onboarding de nuevos clientes (PRIORIDAD MEDIA)

**Qué falta:**
- Wizard de configuración inicial: nombre empresa, RFC, logo, sucursal principal
- Import masivo de productos desde Excel/CSV
- Tour guiado del sistema (tooltips, pasos)

---

### 2.12 Soporte multi-moneda (PRIORIDAD BAJA)

**Qué falta:** Para expansión a otros países (Guatemala, Colombia, etc.):
- Campo `currency` por tenant
- Tipo de cambio configurable
- Reportes en moneda base y en USD

---

### 2.13 Integraciones (PRIORIDAD BAJA)

| Integración | Caso de uso |
|-------------|-------------|
| Contpaqi / Aspel COI | Exportar pólizas contables |
| QuickBooks | Clientes con contabilidad en inglés |
| Mercado Libre | Sincronizar inventario con tienda online |
| WhatsApp Business API | Enviar estados de cuenta y cobros |
| Terminal bancaria (CIE) | Cobro con tarjeta integrado al POS |

---

## 3. Roadmap sugerido (por fases)

### Fase 1 — Base SaaS (3-4 meses)
1. Multi-tenancy con Stancl/Tenancy
2. Sistema de planes y suscripciones + Stripe/Conekta
3. Onboarding wizard
4. Reportes básicos (ventas, utilidad, cxp/cxc)

### Fase 2 — POS y Facturación (2-3 meses)
5. Punto de venta táctil con corte de caja
6. Integración CFDI / SAT (Facturama)
7. Exportación PDF/Excel
8. Órdenes de compra formales

### Fase 3 — CRM y Automatización (2 meses)
9. CRM básico de clientes con límite de crédito
10. Alertas automáticas (email, WhatsApp)
11. Devoluciones (venta y compra)
12. Notificaciones push

### Fase 4 — Escala y Expansión (continuo)
13. Multi-moneda
14. Integraciones contables
15. App móvil mejorada (Android + iOS)
16. Marketplace de módulos adicionales

---

## 4. Modelo de precios sugerido

| Plan | Precio MXN/mes | Sucursales | Usuarios | Productos | Módulos |
|------|---------------|-----------|---------|----------|---------|
| **Básico** | $299 | 1 | 3 | 500 | Inventario, Ventas, Gastos |
| **Estándar** | $599 | 3 | 10 | Ilimitado | + CxP, CxC, Reportes |
| **Premium** | $999 | Ilimitado | Ilimitado | Ilimitado | + CFDI, POS, API móvil |
| **Enterprise** | A convenir | Ilimitado | Ilimitado | Ilimitado | + Integraciones, SLA |

---

## 5. Ventajas competitivas actuales

- **Multi-sucursal nativo** desde el día 1
- **API móvil OAuth 2.0** lista para app Android
- **Auditoría completa** de todas las operaciones
- **Cuentas por Pagar y Cobrar** con historial de pagos parciales
- **Dos tipos de venta** (catálogo y libre/descripción)
- **Stack moderno** (Laravel 12, Vue 3, Inertia) fácil de mantener y escalar
