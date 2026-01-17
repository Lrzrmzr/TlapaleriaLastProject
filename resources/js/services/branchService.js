import api from './api';

const branchService = {
    /**
     * Obtener lista de sucursales
     */
    async getAll(params = {}) {
        return await api.get('/mobile/branches', { params });
    },

    /**
     * Obtener una sucursal por ID
     */
    async getById(id) {
        return await api.get(`/mobile/branches/${id}`);
    },

    /**
     * Obtener estadísticas de una sucursal
     */
    async getStats(id) {
        return await api.get(`/mobile/branches/${id}/stats`);
    },
};

export default branchService;
