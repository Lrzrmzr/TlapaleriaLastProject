import api from './api';
import { Preferences } from '@capacitor/preferences';

const authService = {
    /**
     * Iniciar sesión
     */
    async login(email, password) {
        const response = await api.post('/login', { email, password });

        if (response.success && response.data.token) {
            // Guardar el token y los datos del usuario
            await Preferences.set({
                key: 'auth_token',
                value: response.data.token,
            });

            await Preferences.set({
                key: 'user_data',
                value: JSON.stringify(response.data.user),
            });

            return response.data;
        }

        throw new Error(response.message || 'Error al iniciar sesión');
    },

    /**
     * Cerrar sesión
     */
    async logout() {
        try {
            await api.post('/logout');
        } catch (error) {
            console.error('Error al cerrar sesión en el servidor:', error);
        } finally {
            // Limpiar el almacenamiento local
            await Preferences.remove({ key: 'auth_token' });
            await Preferences.remove({ key: 'user_data' });
        }
    },

    /**
     * Obtener información del usuario actual
     */
    async me() {
        const response = await api.get('/me');

        if (response.success && response.data) {
            // Actualizar los datos del usuario almacenados
            await Preferences.set({
                key: 'user_data',
                value: JSON.stringify(response.data),
            });

            return response.data;
        }

        throw new Error('Error al obtener información del usuario');
    },

    /**
     * Obtener el token almacenado
     */
    async getToken() {
        const { value } = await Preferences.get({ key: 'auth_token' });
        return value;
    },

    /**
     * Obtener los datos del usuario almacenados
     */
    async getUser() {
        const { value } = await Preferences.get({ key: 'user_data' });
        return value ? JSON.parse(value) : null;
    },

    /**
     * Verificar si el usuario está autenticado
     */
    async isAuthenticated() {
        const token = await this.getToken();
        return !!token;
    },

    /**
     * Verificar si el usuario tiene un rol específico
     */
    async hasRole(role) {
        const user = await this.getUser();
        return user && user.role === role;
    },

    /**
     * Verificar si el usuario es root
     */
    async isRoot() {
        return await this.hasRole('root');
    },

    /**
     * Verificar si el usuario es administrador o root
     */
    async isAdmin() {
        const user = await this.getUser();
        return user && (user.role === 'root' || user.role === 'administrador');
    },
};

export default authService;
