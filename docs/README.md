# Documentación - Sistema Ferretería TOTORO

Bienvenido a la documentación del sistema de gestión para Ferretería TOTORO.

## 📋 Índice de Documentación

### API REST
- **[API_RESUMEN.md](API_RESUMEN.md)** - ⚡ Resumen rápido de todos los endpoints
- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - 📖 Documentación completa de la API
- **[OAUTH2_GUIA.md](OAUTH2_GUIA.md)** - 🔐 **NUEVO:** Guía completa de OAuth 2.0
- **[GUIA_TOKENS.md](GUIA_TOKENS.md)** - 🔑 Guía de autenticación (referencia)

### Configuración del Sistema
- **[GUIA_CONFIGURACION.md](GUIA_CONFIGURACION.md)** - ⚙️ Configuración general del sistema
- **[CONFIGURACION_SISTEMA.md](CONFIGURACION_SISTEMA.md)** - 🛠️ Configuración técnica
- **[GUIA_VERIFICACION_RAPIDA.md](GUIA_VERIFICACION_RAPIDA.md)** - ✅ Verificación rápida

### Base de Datos
- **[VERIFICACION_BD.md](VERIFICACION_BD.md)** - 🗄️ Verificación de base de datos

---

## 🚀 Inicio Rápido

### Para usar la API

1. **Lee el resumen:** [API_RESUMEN.md](API_RESUMEN.md)
2. **Auténticate:** Usa `/api/login` para obtener un token
3. **Consulta endpoints:** Usa el token en el header `Authorization: Bearer {token}`

### Para configurar el sistema

1. **Configuración inicial:** [GUIA_CONFIGURACION.md](GUIA_CONFIGURACION.md)
2. **Verificar instalación:** [GUIA_VERIFICACION_RAPIDA.md](GUIA_VERIFICACION_RAPIDA.md)

---

## 📊 Estructura del Sistema

### Frontend
- **Página Pública:** Vista moderna de catálogo de productos (sin precios)
- **Dashboard Administrativo:** Panel de control para empleados/administradores

### Backend
- **API REST:** Endpoints protegidos con Laravel Passport (OAuth 2.0)
- **Autenticación:** OAuth 2.0 con tokens JWT firmados
- **Autorización:** Sistema de roles (empleado/administrador)
- **Base de datos:** MySQL/MariaDB con Laravel Eloquent

### Módulos Principales
- 👥 **Usuarios y Roles**
- 📦 **Productos y Proveedores**
- 📊 **Inventario**
- 💰 **Ventas**
- ⚠️ **Faltantes**
- ⚙️ **Configuración del Sistema**

---

## 🔐 Seguridad

- ✅ **OAuth 2.0** - Protocolo estándar de la industria
- ✅ **Tokens JWT** - Firmados digitalmente, imposibles de falsificar
- ✅ **Expiración automática** - Access tokens expiran en 15 días
- ✅ **Refresh Tokens** - Renovación sin pedir credenciales
- ✅ **Control de acceso basado en roles** - empleado/administrador
- ✅ **Validación de datos** - En todas las peticiones
- ✅ **Separación pública/administrativa** - Endpoints diferenciados

---

## 📝 Notas Importantes

1. **Endpoint público:** `/api/products` no requiere autenticación
2. **Endpoints admin:** Todos bajo `/api/admin/*` requieren access_token + rol
3. **Roles válidos:** `empleado` y `administrador`
4. **Access Tokens:** Son JWT firmados que expiran en 15 días
5. **Autenticación:** Ahora usa OAuth 2.0 (antes Sanctum)

---

## 🆘 Soporte

Para dudas o problemas:
1. Revisa la documentación correspondiente
2. Verifica los logs de Laravel en `storage/logs/`
3. Consulta los códigos de error en [API_RESUMEN.md](API_RESUMEN.md)

---

**Última actualización:** Diciembre 2025
