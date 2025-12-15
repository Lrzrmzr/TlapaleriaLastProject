import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { useAuth } from './composables/useAuth';
import axios from 'axios';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Configurar interceptores de Axios para OAuth 2.0
const { initializeAuth, logout } = useAuth();

// Inicializar token si existe
initializeAuth();

// Interceptor de peticiones: agregar token automáticamente
axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('access_token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Interceptor de respuestas: manejar errores 401 (token expirado/inválido)
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            // Token inválido o expirado - hacer logout
            console.warn('Token expirado o inválido. Cerrando sesión...');
            logout();
        }
        return Promise.reject(error);
    }
);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
