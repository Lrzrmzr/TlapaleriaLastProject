import { ref, computed } from 'vue';
import { authService } from '@/services';

// Estado global de autenticación para móvil
const user = ref(null);
const isLoading = ref(false);
const isInitialized = ref(false);

export function useMobileAuth() {
    /**
     * Inicializar autenticación (cargar usuario desde storage)
     */
    const initialize = async () => {
        if (isInitialized.value) return;

        try {
            isLoading.value = true;
            const userData = await authService.getUser();
            user.value = userData;
            isInitialized.value = true;
        } catch (error) {
            console.error('Error al inicializar autenticación:', error);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Login con email y password
     */
    const login = async (email, password) => {
        try {
            isLoading.value = true;
            const data = await authService.login(email, password);
            user.value = data.user;
            return data;
        } catch (error) {
            console.error('Error en login:', error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Logout
     */
    const logout = async () => {
        try {
            isLoading.value = true;
            await authService.logout();
            user.value = null;
        } catch (error) {
            console.error('Error en logout:', error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Refrescar información del usuario
     */
    const refreshUser = async () => {
        try {
            isLoading.value = true;
            const userData = await authService.me();
            user.value = userData;
            return userData;
        } catch (error) {
            console.error('Error al refrescar usuario:', error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Verificar si está autenticado
     */
    const isAuthenticated = computed(() => !!user.value);

    /**
     * Verificar si el usuario es root
     */
    const isRoot = computed(() => user.value?.role === 'root');

    /**
     * Verificar si el usuario es administrador o root
     */
    const isAdmin = computed(() =>
        user.value?.role === 'root' || user.value?.role === 'administrador'
    );

    /**
     * Obtener la sucursal del usuario
     */
    const userBranch = computed(() => user.value?.branch);

    /**
     * Verificar si tiene un rol específico
     */
    const hasRole = (role) => {
        return user.value?.role === role;
    };

    return {
        // Estado
        user,
        isLoading,
        isAuthenticated,
        isRoot,
        isAdmin,
        userBranch,

        // Métodos
        initialize,
        login,
        logout,
        refreshUser,
        hasRole,
    };
}
