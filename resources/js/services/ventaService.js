import api from './api';

const ventaService = {
    /**
     * Obtener lista de ventas
     */
    async getAll(params = {}) {
        return await api.get('/mobile/ventas', { params });
    },

    /**
     * Obtener una venta por ID
     */
    async getById(id) {
        return await api.get(`/mobile/ventas/${id}`);
    },

    /**
     * Crear una nueva venta
     */
    async create(data) {
        return await api.post('/mobile/ventas', data);
    },

    /**
     * Eliminar una venta
     */
    async delete(id) {
        return await api.delete(`/mobile/ventas/${id}`);
    },
};

export default ventaService;
