import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

// Estado global de autenticación
const token = ref(localStorage.getItem('access_token') || null);
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));

export function useAuth() {

    /**
     * Verifica si el usuario está autenticado
     */
    const isAuthenticated = computed(() => !!token.value);

    /**
     * Login con OAuth 2.0
     * @param {Object} credentials - { email, password }
     * @returns {Promise}
     */
    const login = async (credentials) => {
        try {
            const response = await axios.post('/api/login', credentials);

            const { access_token, user: userData } = response.data;

            // Guardar token y usuario en localStorage
            token.value = access_token;
            user.value = userData;

            localStorage.setItem('access_token', access_token);
            localStorage.setItem('user', JSON.stringify(userData));

            // Configurar header de autorización para futuras peticiones
            axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;

            return response.data;
        } catch (error) {
            console.error('Error en login:', error);
            throw error;
        }
    };

    /**
     * Logout - Revoca el token
     */
    const logout = async () => {
        try {
            // Intentar revocar el token en el servidor
            if (token.value) {
                await axios.post('/api/logout', {}, {
                    headers: { 'Authorization': `Bearer ${token.value}` }
                });
            }
        } catch (error) {
            console.error('Error al revocar token:', error);
        } finally {
            // Limpiar estado local
            token.value = null;
            user.value = null;

            localStorage.removeItem('access_token');
            localStorage.removeItem('user');

            delete axios.defaults.headers.common['Authorization'];

            // Forzar recarga completa a la página de login
            // Usamos window.location en lugar de router.visit para limpiar todo el estado de Inertia
            window.location.href = '/login';
        }
    };

    /**
     * Obtiene el token actual
     */
    const getToken = () => token.value;

    /**
     * Obtiene el usuario actual
     */
    const getUser = () => user.value;

    /**
     * Verifica si el usuario tiene un rol específico
     * @param {String} roleName - Nombre del rol
     */
    const hasRole = (roleName) => {
        if (!user.value || !user.value.roles) return false;
        return user.value.roles.includes(roleName);
    };

    /**
     * Verifica si el token está expirado (básico)
     * Nota: El servidor también valida esto
     */
    const isTokenExpired = () => {
        // Por ahora retornamos false, el servidor manejará la expiración
        // TODO: Implementar validación de JWT en cliente si es necesario
        return false;
    };

    /**
     * Inicializa el token en axios si existe
     */
    const initializeAuth = () => {
        if (token.value) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
        }
    };

    return {
        // Estado
        token,
        user,
        isAuthenticated,

        // Métodos
        login,
        logout,
        getToken,
        getUser,
        hasRole,
        isTokenExpired,
        initializeAuth,
    };
}
