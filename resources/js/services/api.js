import axios from 'axios';
import { Preferences } from '@capacitor/preferences';

// URL base de la API
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Crear instancia de axios
const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    timeout: 30000, // 30 segundos
});

// Interceptor para agregar el token a las peticiones
api.interceptors.request.use(
    async (config) => {
        try {
            // Obtener el token almacenado
            const { value: token } = await Preferences.get({ key: 'auth_token' });

            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
        } catch (error) {
            console.error('Error al obtener el token:', error);
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Interceptor para manejar respuestas y errores
api.interceptors.response.use(
    (response) => {
        // Retornar solo la data si la respuesta es exitosa
        return response.data;
    },
    async (error) => {
        if (error.response) {
            // El servidor respondió con un código de estado fuera del rango 2xx
            const { status, data } = error.response;

            if (status === 401) {
                // Token inválido o expirado
                await Preferences.remove({ key: 'auth_token' });
                await Preferences.remove({ key: 'user_data' });

                // Redirigir al login (esto depende de tu router)
                if (window.location.pathname !== '/login') {
                    window.location.href = '/login';
                }
            }

            // Retornar el mensaje de error del servidor
            return Promise.reject({
                message: data.message || 'Error del servidor',
                errors: data.errors || {},
                status,
            });
        } else if (error.request) {
            // La petición fue hecha pero no hubo respuesta
            return Promise.reject({
                message: 'No se pudo conectar con el servidor. Verifica tu conexión a internet.',
                status: 0,
            });
        } else {
            // Algo pasó al configurar la petición
            return Promise.reject({
                message: error.message || 'Error desconocido',
                status: -1,
            });
        }
    }
);

export default api;
