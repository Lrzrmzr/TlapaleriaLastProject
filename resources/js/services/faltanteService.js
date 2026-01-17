import api from './api';

const faltanteService = {
    /**
     * Obtener lista de faltantes
     */
    async getAll(params = {}) {
        return await api.get('/mobile/faltantes', { params });
    },

    /**
     * Obtener un faltante por ID
     */
    async getById(id) {
        return await api.get(`/mobile/faltantes/${id}`);
    },

    /**
     * Crear un nuevo faltante
     */
    async create(data) {
        return await api.post('/mobile/faltantes', data);
    },

    /**
     * Actualizar un faltante
     */
    async update(id, data) {
        return await api.put(`/mobile/faltantes/${id}`, data);
    },

    /**
     * Marcar faltante como completado
     */
    async markAsCompleted(id) {
        return await api.post(`/mobile/faltantes/${id}/complete`);
    },

    /**
     * Eliminar un faltante
     */
    async delete(id) {
        return await api.delete(`/mobile/faltantes/${id}`);
    },
};

export default faltanteService;
