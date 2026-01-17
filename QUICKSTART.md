# 🚀 QUICK START - Desarrollo Móvil

## ⚡ Inicio Rápido en 5 Minutos

### 1️⃣ Verificar Instalación (30 segundos)

```bash
# Verificar que todo está instalado
php artisan --version
npm --version
npx cap --version
```

### 2️⃣ Iniciar Servidor Laravel (30 segundos)

```bash
# Terminal 1
php artisan serve
```

Deberías ver: `Server running on [http://127.0.0.1:8000]`

### 3️⃣ Probar la API (1 minuto)

Abre una nueva terminal:

```bash
# Probar login (ajusta email y password)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"tu-email@example.com\",\"password\":\"tu-password\"}"
```

Si ves algo como esto, ¡funciona! ✅
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV..."
  }
}
```

### 4️⃣ Compilar Assets (1 minuto)

```bash
# Terminal 2
npm run build
```

### 5️⃣ Sincronizar con Android (1 minuto)

```bash
npx cap sync android
```

### 6️⃣ Abrir en Android Studio (1 minuto)

```bash
npx cap open android
```

En Android Studio:
1. Espera a que Gradle termine de sincronizar
2. Conecta un dispositivo Android o inicia un emulador
3. Click en el botón **Run** (▶️)
4. ¡La app se instalará y abrirá!

---

## 📱 Primera Vista - Login Móvil

### Opción A: Usar Inertia (Recomendado para este proyecto)

Crea: `resources/js/Pages/Mobile/Login.vue`

```vue
<template>
  <div class="min-h-screen bg-gradient-to-b from-blue-600 to-blue-800 flex items-center justify-center p-6">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Ferretería TOTORO</h1>
        <p class="text-gray-600 mt-2">Sistema de Gestión</p>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="handleLogin" class="space-y-6">
        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Correo Electrónico
          </label>
          <input
            v-model="form.email"
            type="email"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="usuario@example.com"
            required
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Contraseña
          </label>
          <input
            v-model="form.password"
            type="password"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="••••••••"
            required
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loading">Iniciando sesión...</span>
          <span v-else>Iniciar Sesión</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useMobileAuth } from '@/composables/useMobileAuth';

const { login } = useMobileAuth();

const form = ref({
  email: '',
  password: ''
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  try {
    loading.value = true;
    error.value = '';

    await login(form.value.email, form.value.password);

    // Redirigir al dashboard
    window.location.href = '/dashboard';

  } catch (err) {
    error.value = err.message || 'Error al iniciar sesión. Verifica tus credenciales.';
  } finally {
    loading.value = false;
  }
};
</script>
```

### Crear Ruta en Laravel

Edita `routes/web.php`:

```php
use Inertia\Inertia;

// Ruta para login móvil
Route::get('/mobile/login', function () {
    return Inertia::render('Mobile/Login');
})->name('mobile.login');
```

---

## 🧪 Probar en el Navegador Primero

Antes de compilar para Android, prueba en el navegador:

```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite
npm run dev
```

Abre: `http://localhost:8000/mobile/login`

---

## 📲 Comandos Útiles del Día a Día

### Desarrollo Web
```bash
# Iniciar servidor Laravel
php artisan serve

# Iniciar Vite (hot reload)
npm run dev

# Ver rutas
php artisan route:list
```

### Desarrollo Móvil
```bash
# Compilar assets
npm run build

# Sincronizar con Android
npx cap sync android

# Solo copiar assets (más rápido)
npx cap copy android

# Abrir Android Studio
npx cap open android

# Ver logs de la app
adb logcat | grep -i capacitor
```

### Debugging
```bash
# Limpiar cache de Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Limpiar node_modules (si hay problemas)
rm -rf node_modules package-lock.json
npm install

# Ver errores de Laravel
tail -f storage/logs/laravel.log
```

---

## 🔑 Datos de Prueba

Si necesitas crear un usuario de prueba:

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@totoro.com';
$user->password = bcrypt('password123');
$user->branch_id = 1; // Ajustar según tu BD
$user->save();

// Asignar rol (si usas Spatie Roles)
$user->assignRole('root');
```

---

## 📱 Configuración de Dispositivo Android

### Habilitar Modo Desarrollador

1. **Configuración** → **Acerca del teléfono**
2. Toca **Número de compilación** 7 veces
3. Vuelve a Configuración → **Opciones de desarrollador**
4. Activa **Depuración USB**

### Conectar por USB

1. Conecta el dispositivo a tu PC
2. En el dispositivo, acepta la autorización de depuración
3. Verifica conexión: `adb devices`

### Usar Emulador

En Android Studio:
1. **Tools** → **Device Manager**
2. **Create Device**
3. Seleccionar un dispositivo (ej: Pixel 5)
4. Seleccionar imagen del sistema (Android 11+)
5. Finish y click en ▶️ para iniciar

---

## 🌐 Conectar desde Dispositivo Real

### Si Laravel está en tu PC local:

1. **Encuentra tu IP local**:
   ```bash
   # Windows
   ipconfig
   # Buscar "IPv4 Address" (ej: 192.168.1.100)

   # Mac/Linux
   ifconfig
   # Buscar "inet" en tu interface de red
   ```

2. **Actualizar capacitor.config.json**:
   ```json
   {
     "server": {
       "url": "http://192.168.1.100:8000",
       "cleartext": true
     }
   }
   ```

3. **Actualizar .env**:
   ```
   VITE_API_URL=http://192.168.1.100:8000/api
   ```

4. **Permitir conexiones externas en Laravel**:
   ```bash
   php artisan serve --host=0.0.0.0
   ```

5. **Sincronizar de nuevo**:
   ```bash
   npm run build
   npx cap sync android
   ```

---

## 🎯 Flujo de Trabajo Recomendado

### Ciclo de Desarrollo

```
1. Desarrollar en el navegador (más rápido)
   ↓
2. npm run dev (hot reload)
   ↓
3. Probar funcionalidad
   ↓
4. Cuando esté listo:
   npm run build
   npx cap copy android
   ↓
5. Probar en Android Studio
   ↓
6. Iterar
```

### Para Cambios de CSS/JS

```bash
# No necesitas rebuild completo
npm run build && npx cap copy android
```

### Para Cambios de Plugins Capacitor

```bash
# Necesitas sync completo
npx cap sync android
```

---

## 🐛 Solución Rápida de Problemas

### "No se puede conectar a la API"
```bash
# 1. Verificar que Laravel esté corriendo
curl http://localhost:8000/api/me

# 2. Verificar CORS en config/cors.php
# 3. Verificar que el token esté en los headers
```

### "La app no se actualiza"
```bash
# Limpiar todo y reconstruir
npm run build
npx cap sync android
# Luego en Android Studio: Build → Clean Project
```

### "Token inválido"
```bash
# Logout y login de nuevo
# O limpiar Preferences:
# En DevTools → Application → Clear Storage
```

---

## 📚 Documentación Completa

Para más detalles, consulta:

- **API Reference**: [docs/API_MOBILE.md](docs/API_MOBILE.md)
- **Configuración Backend**: [docs/API_SETUP_COMPLETE.md](docs/API_SETUP_COMPLETE.md)
- **Configuración Capacitor**: [docs/CAPACITOR_SETUP.md](docs/CAPACITOR_SETUP.md)
- **Resumen Completo**: [CONFIGURACION_COMPLETA.md](CONFIGURACION_COMPLETA.md)

---

## 💡 Tips

1. **Siempre prueba en el navegador primero** - Es más rápido
2. **Usa `npm run dev` para desarrollo** - Hot reload automático
3. **Solo usa `npm run build` cuando vayas a probar en móvil**
4. **Guarda el token después del login** - Para no tener que loguearte cada vez
5. **Usa Chrome DevTools para debug** - chrome://inspect cuando la app está corriendo

---

## ✅ Checklist de Primera Vez

- [ ] Laravel corriendo en http://localhost:8000
- [ ] Probaste el login con curl
- [ ] npm run build completado
- [ ] Android Studio abierto
- [ ] Dispositivo/emulador conectado
- [ ] App instalada y corriendo
- [ ] Puedes ver la pantalla web en el móvil

---

**¡Estás listo para desarrollar! 🎉**

Si tienes dudas, revisa la documentación completa o los ejemplos en los archivos de servicios.
