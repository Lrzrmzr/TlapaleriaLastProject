import api from './api';

const gastoService = {
    /**
     * Obtener lista de gastos
     */
    async getAll(params = {}) {
        return await api.get('/mobile/gastos', { params });
    },

    /**
     * Obtener un gasto por ID
     */
    async getById(id) {
        return await api.get(`/mobile/gastos/${id}`);
    },

    /**
     * Crear un nuevo gasto
     */
    async create(data) {
        return await api.post('/mobile/gastos', data);
    },

    /**
     * Actualizar un gasto
     */
    async update(id, data) {
        return await api.put(`/mobile/gastos/${id}`, data);
    },

    /**
     * Eliminar un gasto
     */
    async delete(id) {
        return await api.delete(`/mobile/gastos/${id}`);
    },
};

export default gastoService;
