import api from './api';

const productService = {
    /**
     * Obtener lista de productos
     */
    async getAll(params = {}) {
        return await api.get('/mobile/products', { params });
    },

    /**
     * Obtener un producto por ID
     */
    async getById(id) {
        return await api.get(`/mobile/products/${id}`);
    },

    /**
     * Buscar producto por código de barras
     */
    async searchByBarcode(barcode) {
        return await api.get(`/mobile/products/barcode/${barcode}`);
    },

    /**
     * Crear un nuevo producto
     */
    async create(data) {
        return await api.post('/mobile/products', data);
    },

    /**
     * Actualizar un producto
     */
    async update(id, data) {
        return await api.put(`/mobile/products/${id}`, data);
    },

    /**
     * Eliminar un producto
     */
    async delete(id) {
        return await api.delete(`/mobile/products/${id}`);
    },
};

export default productService;
