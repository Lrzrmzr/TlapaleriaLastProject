# Configuración de Capacitor - Aplicación Móvil

## ✅ Configuración Completada

### 📦 Paquetes Instalados

```json
{
  "@capacitor/core": "^8.0.0",
  "@capacitor/cli": "^8.0.0",
  "@capacitor/android": "^8.0.0",
  "@capacitor/preferences": "^8.0.0",
  "@capacitor/network": "^8.0.0",
  "@capacitor/camera": "^8.0.0",
  "@capacitor/splash-screen": "^8.0.0"
}
```

### 🔧 Configuración Inicial

**Archivo**: `capacitor.config.json`

```json
{
  "appId": "com.totoro.tlapaleria",
  "appName": "Ferreteria TOTORO",
  "webDir": "public",
  "server": {
    "url": "http://localhost:8000",
    "cleartext": true,
    "androidScheme": "http"
  },
  "plugins": {
    "SplashScreen": {
      "launchShowDuration": 2000,
      "backgroundColor": "#1e40af",
      "showSpinner": true,
      "spinnerColor": "#ffffff"
    }
  }
}
```

---

## 📱 Plugins Instalados

### 1. @capacitor/preferences
**Propósito**: Almacenamiento de datos clave-valor (reemplaza localStorage en móvil)

**Uso**: Guardar token de autenticación y datos del usuario

```javascript
import { Preferences } from '@capacitor/preferences';

// Guardar
await Preferences.set({
  key: 'auth_token',
  value: token,
});

// Obtener
const { value } = await Preferences.get({ key: 'auth_token' });

// Eliminar
await Preferences.remove({ key: 'auth_token' });
```

### 2. @capacitor/network
**Propósito**: Monitorear el estado de la conexión a internet

**Uso**: Detectar cuando el dispositivo pierde/recupera conexión

```javascript
import { Network } from '@capacitor/network';

// Obtener estado actual
const status = await Network.getStatus();
console.log('Network status:', status);

// Escuchar cambios
Network.addListener('networkStatusChange', status => {
  console.log('Network status changed', status);
});
```

### 3. @capacitor/camera
**Propósito**: Acceso a la cámara del dispositivo

**Uso**: Escanear códigos de barras de productos (requiere plugin adicional para barcode)

```javascript
import { Camera, CameraResultType } from '@capacitor/camera';

const image = await Camera.getPhoto({
  quality: 90,
  allowEditing: false,
  resultType: CameraResultType.Uri
});
```

### 4. @capacitor/splash-screen
**Propósito**: Pantalla de carga al iniciar la app

**Uso**: Mostrar logo de Ferretería TOTORO mientras carga

```javascript
import { SplashScreen } from '@capacitor/splash-screen';

// Ocultar splash screen
await SplashScreen.hide();
```

---

## 🗂️ Estructura de Servicios Creada

```
resources/js/services/
├── api.js                  # Configuración base de axios con interceptores
├── authService.js          # Servicio de autenticación
├── productService.js       # Servicio de productos
├── inventoryService.js     # Servicio de inventario
├── ventaService.js         # Servicio de ventas
├── gastoService.js         # Servicio de gastos
├── faltanteService.js      # Servicio de faltantes
├── branchService.js        # Servicio de sucursales
└── index.js                # Exportaciones centralizadas
```

### Características de los Servicios:

✅ **Interceptores de Axios**:
- Agrega automáticamente el token de autenticación a todas las peticiones
- Maneja errores 401 (no autenticado) redirigiendo al login
- Maneja errores de conexión mostrando mensajes amigables

✅ **Almacenamiento con Capacitor Preferences**:
- Token de autenticación persistente
- Datos del usuario offline
- Compatible con iOS y Android

✅ **Manejo de Errores**:
- Mensajes descriptivos en español
- Detección de falta de conexión
- Validación de respuestas del servidor

---

## 🎯 Composables de Vue Creados

### useMobileAuth.js
Composable para manejar autenticación en la app móvil

```javascript
import { useMobileAuth } from '@/composables/useMobileAuth';

const {
  user,
  isLoading,
  isAuthenticated,
  isRoot,
  isAdmin,
  userBranch,
  initialize,
  login,
  logout,
  refreshUser,
  hasRole,
} = useMobileAuth();

// Inicializar al cargar la app
await initialize();

// Login
await login('admin@example.com', 'password');

// Logout
await logout();

// Verificar rol
if (isAdmin.value) {
  // Usuario es admin o root
}
```

---

## 🚀 Comandos Capacitor

### Desarrollo

```bash
# Sincronizar cambios web con Android
npx cap sync android

# Copiar assets web a Android (sin sincronizar plugins)
npx cap copy android

# Actualizar plugins nativos
npx cap update android

# Abrir proyecto en Android Studio
npx cap open android
```

### Build de Producción

```bash
# 1. Compilar aplicación web
npm run build

# 2. Copiar a Android
npx cap copy android

# 3. Abrir en Android Studio para generar APK/AAB
npx cap open android
```

---

## 📲 Desarrollo en Android

### Requisitos Previos:

1. **Android Studio** instalado
2. **Java JDK 17** (recomendado)
3. **Android SDK** configurado
4. **Dispositivo Android** o emulador

### Pasos para Ejecutar:

1. **Abrir Android Studio**:
   ```bash
   npx cap open android
   ```

2. **Configurar dispositivo**:
   - Conectar dispositivo Android con USB debugging
   - O usar un emulador AVD

3. **Ejecutar la app**:
   - Click en el botón "Run" (▶️) en Android Studio
   - Seleccionar dispositivo/emulador
   - La app se instalará y ejecutará automáticamente

### Depuración:

**Ver logs en tiempo real**:
```bash
# Desde Android Studio: Logcat
# O desde terminal:
adb logcat | grep Capacitor
```

**Chrome DevTools** (para depurar WebView):
1. Conectar dispositivo/emulador
2. Abrir Chrome: `chrome://inspect`
3. Seleccionar la WebView de la app
4. Usar DevTools normalmente

---

## 🔐 Configuración de Desarrollo vs Producción

### Desarrollo (archivo actual):
```json
{
  "server": {
    "url": "http://localhost:8000",
    "cleartext": true,
    "androidScheme": "http"
  }
}
```

Esto permite que la app móvil se conecte a tu servidor Laravel local.

### Producción:
```json
{
  "server": {
    "androidScheme": "https"
  }
}
```

Para producción:
1. Eliminar la configuración `server.url`
2. Cambiar `androidScheme` a `https`
3. La app usará los archivos compilados en `public/`
4. Configurar variable de entorno `VITE_API_URL` con la URL de producción

---

## 📝 Variables de Entorno

Crear archivo `.env` con:

```env
# URL del servidor API
VITE_API_URL=http://localhost:8000/api

# Para producción:
# VITE_API_URL=https://tu-dominio.com/api
```

Los servicios usarán esta variable automáticamente.

---

## 🎨 Personalización de la App

### Icono de la App:

1. Crear iconos en diferentes resoluciones (obligatorio):
   - `android/app/src/main/res/mipmap-hdpi/ic_launcher.png` (72x72)
   - `android/app/src/main/res/mipmap-mdpi/ic_launcher.png` (48x48)
   - `android/app/src/main/res/mipmap-xhdpi/ic_launcher.png` (96x96)
   - `android/app/src/main/res/mipmap-xxhdpi/ic_launcher.png` (144x144)
   - `android/app/src/main/res/mipmap-xxxhdpi/ic_launcher.png` (192x192)

2. Herramienta recomendada: [Android Asset Studio](https://romannurik.github.io/AndroidAssetStudio/icons-launcher.html)

### Splash Screen:

Editar `capacitor.config.json`:
```json
{
  "plugins": {
    "SplashScreen": {
      "launchShowDuration": 3000,
      "backgroundColor": "#1e40af",
      "androidSplashResourceName": "splash",
      "showSpinner": true,
      "spinnerColor": "#ffffff"
    }
  }
}
```

Agregar imagen splash en:
- `android/app/src/main/res/drawable/splash.png`

### Nombre de la App:

Editar `android/app/src/main/res/values/strings.xml`:
```xml
<resources>
    <string name="app_name">Ferreteria TOTORO</string>
    <string name="title_activity_main">Ferreteria TOTORO</string>
</resources>
```

---

## 🔒 Permisos de Android

Editar `android/app/src/main/AndroidManifest.xml`:

```xml
<manifest>
    <!-- Permisos necesarios -->
    <uses-permission android:name="android.permission.INTERNET" />
    <uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />

    <!-- Para la cámara (si se usa) -->
    <uses-permission android:name="android.permission.CAMERA" />

    <!-- Para almacenamiento -->
    <uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE"/>
    <uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE"/>
</manifest>
```

---

## 📋 Checklist de Desarrollo Móvil

### Antes de Empezar:
- [x] Capacitor instalado y configurado
- [x] Plataforma Android agregada
- [x] Plugins instalados (Preferences, Network, Camera, SplashScreen)
- [x] Servicios de API creados
- [x] Composable de autenticación móvil creado

### Durante Desarrollo:
- [ ] Crear páginas/vistas móviles optimizadas
- [ ] Implementar navegación móvil (bottom navigation o drawer)
- [ ] Integrar servicios con las vistas
- [ ] Manejar estados de carga
- [ ] Implementar manejo de errores amigable
- [ ] Optimizar para pantallas pequeñas
- [ ] Probar en diferentes tamaños de pantalla
- [ ] Implementar modo offline (opcional)

### Antes de Producción:
- [ ] Cambiar `capacitor.config.json` para producción
- [ ] Configurar icono de la app
- [ ] Configurar splash screen
- [ ] Probar en dispositivos reales
- [ ] Optimizar rendimiento
- [ ] Generar keystore para firma de APK
- [ ] Configurar versionado (version code y version name)
- [ ] Generar APK/AAB de release
- [ ] Probar APK de release

---

## 🛠️ Solución de Problemas

### Error: "Cleartext HTTP traffic not permitted"
**Solución**: Ya está configurado en `capacitor.config.json` con `"cleartext": true`

### Error: "Unable to load assets"
**Solución**:
```bash
npm run build
npx cap sync android
```

### Error: Token no se guarda
**Solución**: Verificar que estés usando `Preferences` de Capacitor, no `localStorage`

### App no se conecta a Laravel local
**Solución**:
1. Verificar que Laravel esté corriendo en `http://localhost:8000`
2. Si usas emulador, cambiar a `http://10.0.2.2:8000` (IP del host en emulador)
3. Si usas dispositivo físico, usar la IP de tu PC en la red local

---

## 📚 Recursos Adicionales

- [Documentación Oficial de Capacitor](https://capacitorjs.com/docs)
- [Capacitor Plugins](https://capacitorjs.com/docs/plugins)
- [Guía de Desarrollo Android](https://capacitorjs.com/docs/android)
- [Capacitor Community Plugins](https://github.com/capacitor-community)

---

## 🎯 Próximos Pasos Recomendados

1. **Crear Vista de Login Móvil**
   - Diseño optimizado para móvil
   - Usar `useMobileAuth` composable
   - Validación de formularios

2. **Implementar Navegación**
   - Bottom Navigation Bar
   - Tabs para módulos principales (Productos, Inventario, Ventas, etc.)

3. **Dashboard Móvil**
   - Estadísticas de la sucursal
   - Acceso rápido a funciones frecuentes
   - Notificaciones de stock bajo

4. **Escáner de Códigos de Barras** (opcional)
   - Instalar plugin de barcode scanner
   - Integrar con búsqueda de productos
   - Facilitar el registro de ventas

5. **Modo Offline** (opcional)
   - Implementar cache con Vuex/Pinia
   - Sincronizar cuando hay conexión
   - Notificar al usuario del estado

---

**Fecha de Configuración**: 2024-12-21
**Versión de Capacitor**: 8.0.0
**Plataforma**: Android
