import api from './api';

const inventoryService = {
    /**
     * Obtener lista de inventario
     */
    async getAll(params = {}) {
        return await api.get('/mobile/inventory', { params });
    },

    /**
     * Obtener un inventario por ID
     */
    async getById(id) {
        return await api.get(`/mobile/inventory/${id}`);
    },

    /**
     * Crear un nuevo inventario
     */
    async create(data) {
        return await api.post('/mobile/inventory', data);
    },

    /**
     * Ajustar stock de inventario
     */
    async adjustStock(id, adjustment, reason) {
        return await api.post(`/mobile/inventory/${id}/adjust`, {
            adjustment,
            reason,
        });
    },

    /**
     * Obtener productos sin inventario
     */
    async getProductsWithoutInventory(branchId) {
        return await api.get('/mobile/inventory/productos-sin-inventario', {
            params: { branch_id: branchId },
        });
    },
};

export default inventoryService;
