<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    productos: Object, // Ahora es un objeto con datos de paginación
    proveedores: Array,
    categorias: Array,
    stats: Object,
    filters: Object,
});

// Estados
const showModalNuevo = ref(false);
const showModalEditar = ref(false);
const showModalProveedores = ref(false);
const showModalAgregarProveedor = ref(false);
const showModalEditarProveedor = ref(false);
const productoSeleccionado = ref(null);
const proveedorSeleccionado = ref(null);

// Filtros (inicializados desde el servidor)
const busqueda = ref(props.filters?.search || '');
const filtroProveedor = ref(props.filters?.supplier_id || '');
const filtroCategoria = ref(props.filters?.category_id || '');
const filtroStock = ref(props.filters?.with_stock ? 'con_stock' : (props.filters?.without_inventory ? 'sin_inventario' : ''));
const itemsPorPagina = ref(props.filters?.per_page || 50);

// Forms
const formNuevo = useForm({
    name: '',
    description: '',
    barcode: '',
    price: 0,
    cost: 0,
    supplier_id: null,
    selected_suppliers: [], // Array de IDs de proveedores seleccionados
    category_ids: [], // Array de IDs de categorías
});

const formEditar = useForm({
    id: null,
    name: '',
    description: '',
    barcode: '',
    price: 0,
    cost: 0,
    supplier_id: null,
    category_ids: [], // Array de IDs de categorías
});

const formAgregarProveedor = useForm({
    supplier_id: null,
    cost: 0,
    supplier_code: '',
    is_preferred: false,
    notes: '',
});

const formEditarProveedor = useForm({
    cost: 0,
    supplier_code: '',
    is_preferred: false,
    notes: '',
});

// Computed
const productosFiltrados = computed(() => {
    // Ahora los productos ya vienen filtrados del servidor
    return props.productos.data || [];
});

// Función para aplicar filtros (del lado del servidor)
let filtroTimeout = null;
function aplicarFiltros() {
    clearTimeout(filtroTimeout);
    filtroTimeout = setTimeout(() => {
        const params = {
            search: busqueda.value || undefined,
            supplier_id: filtroProveedor.value || undefined,
            category_id: filtroCategoria.value || undefined,
            with_stock: filtroStock.value === 'con_stock' ? true : undefined,
            without_inventory: filtroStock.value === 'sin_inventario' ? true : undefined,
            per_page: itemsPorPagina.value,
        };

        // Limpiar parámetros undefined
        Object.keys(params).forEach(key => params[key] === undefined && delete params[key]);

        router.get(route('productos.index'), params, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300); // Debounce de 300ms
}

// Función para limpiar filtros
function limpiarFiltros() {
    busqueda.value = '';
    filtroProveedor.value = '';
    filtroCategoria.value = '';
    filtroStock.value = '';
    aplicarFiltros();
}

// Watchers para aplicar filtros automáticamente
watch([busqueda, filtroProveedor, filtroCategoria, filtroStock], () => {
    aplicarFiltros();
});

const proveedoresDisponibles = computed(() => {
    if (!productoSeleccionado.value) return props.proveedores;

    const proveedoresAsignados = productoSeleccionado.value.suppliers_list.map(s => s.id);
    return props.proveedores.filter(p => !proveedoresAsignados.includes(p.id));
});

// Funciones
function abrirModalNuevo() {
    formNuevo.reset();
    showModalNuevo.value = true;
}

function submitNuevo() {
    formNuevo.post(route('productos.store'), {
        onSuccess: () => {
            formNuevo.reset();
            showModalNuevo.value = false;
        },
    });
}

function abrirModalEditar(producto) {
    productoSeleccionado.value = producto;
    formEditar.id = producto.id;
    formEditar.name = producto.name;
    formEditar.description = producto.description;
    formEditar.barcode = producto.barcode;
    formEditar.price = producto.price;
    formEditar.cost = producto.cost;
    formEditar.supplier_id = producto.supplier_id;
    formEditar.category_ids = producto.categories ? producto.categories.map(cat => cat.id) : [];
    showModalEditar.value = true;
}

function submitEditar() {
    formEditar.put(route('productos.update', formEditar.id), {
        onSuccess: () => {
            formEditar.reset();
            showModalEditar.value = false;
            productoSeleccionado.value = null;
        },
    });
}

function eliminarProducto(id) {
    if (confirm('¿Estás seguro de eliminar este producto? Esta acción no se puede deshacer.')) {
        router.delete(route('productos.destroy', id));
    }
}

function abrirModalProveedores(producto) {
    productoSeleccionado.value = producto;
    showModalProveedores.value = true;
}

function abrirModalAgregarProveedor() {
    formAgregarProveedor.reset();
    showModalAgregarProveedor.value = true;
}

function submitAgregarProveedor() {
    formAgregarProveedor.post(route('productos.proveedores.attach', productoSeleccionado.value.id), {
        onSuccess: () => {
            formAgregarProveedor.reset();
            showModalAgregarProveedor.value = false;
        },
    });
}

function abrirModalEditarProveedor(proveedor) {
    proveedorSeleccionado.value = proveedor;
    formEditarProveedor.cost = proveedor.cost;
    formEditarProveedor.supplier_code = proveedor.supplier_code;
    formEditarProveedor.is_preferred = proveedor.is_preferred;
    formEditarProveedor.notes = proveedor.notes;
    showModalEditarProveedor.value = true;
}

function submitEditarProveedor() {
    formEditarProveedor.put(route('productos.proveedores.update', {
        producto: productoSeleccionado.value.id,
        supplier: proveedorSeleccionado.value.id
    }), {
        onSuccess: () => {
            formEditarProveedor.reset();
            showModalEditarProveedor.value = false;
            proveedorSeleccionado.value = null;
        },
    });
}

function eliminarProveedor(supplierId) {
    if (confirm('¿Eliminar este proveedor del producto?')) {
        router.delete(route('productos.proveedores.detach', {
            producto: productoSeleccionado.value.id,
            supplier: supplierId
        }));
    }
}

function formatMoney(value) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
}
</script>

<template>
    <Head title="Productos - Ferretería TOTORO" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                        Catálogo de Productos
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Gestiona tu catálogo completo de productos
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('inventario.index')"
                        class="bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Ver Inventario
                    </Link>
                    <button
                        @click="abrirModalNuevo"
                        class="bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Producto
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400 uppercase">Total Productos</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.total }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-500 to-cyan-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 dark:text-green-400 uppercase">Con Stock</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.conStock }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-emerald-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-orange-600 dark:text-orange-400 uppercase">Sin Inventario</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.sinInventario }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-orange-500 to-amber-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-600 dark:text-purple-400 uppercase">Valor Total</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ formatMoney(stats.valorTotal) }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-purple-500 to-pink-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros y Búsqueda -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Buscar producto:</label>
                            <input
                                v-model="busqueda"
                                type="text"
                                placeholder="Nombre, código de barras o descripción..."
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Proveedor:</label>
                            <select
                                v-model="filtroProveedor"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white min-w-[180px]"
                            >
                                <option value="">Todos los proveedores</option>
                                <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                                    {{ proveedor.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Categoría:</label>
                            <select
                                v-model="filtroCategoria"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white min-w-[180px]"
                            >
                                <option value="">Todas las categorías</option>
                                <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                    {{ categoria.icon }} {{ categoria.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Stock:</label>
                            <select
                                v-model="filtroStock"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Todos</option>
                                <option value="con_stock">Con stock</option>
                                <option value="sin_stock">Sin stock</option>
                                <option value="sin_inventario">Sin inventario</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-4 mt-6">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Mostrando {{ productos.from || 0 }} - {{ productos.to || 0 }} de {{ productos.total || 0 }} productos
                            </div>
                            <button
                                v-if="busqueda || filtroProveedor || filtroCategoria || filtroStock"
                                @click="limpiarFiltros"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Productos -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-7 h-7 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Listado de Productos
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Producto
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Código
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Categorías
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Proveedores
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Precio Venta
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Stock
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="productosFiltrados.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-lg font-medium">No hay productos</p>
                                        <p class="text-sm mt-1">Agrega productos usando el botón de arriba</p>
                                    </td>
                                </tr>
                                <tr v-for="producto in productosFiltrados" :key="producto.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ producto.name }}
                                        </div>
                                        <div v-if="producto.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ producto.description }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ producto.barcode || 'Sin código' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="producto.categories && producto.categories.length > 0" class="flex flex-wrap gap-1">
                                            <span
                                                v-for="categoria in producto.categories"
                                                :key="categoria.id"
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium border"
                                                :style="{
                                                    backgroundColor: categoria.color + '20',
                                                    borderColor: categoria.color,
                                                    color: categoria.color
                                                }"
                                            >
                                                <span class="mr-1">{{ categoria.icon }}</span>
                                                {{ categoria.name }}
                                            </span>
                                        </div>
                                        <span v-else class="text-xs text-gray-400 italic">Sin categoría</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="producto.suppliers_count > 0" class="flex items-center gap-2">
                                            <button
                                                @click="abrirModalProveedores(producto)"
                                                class="inline-flex items-center px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 rounded-lg text-xs font-semibold hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition-colors"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                {{ producto.suppliers_count }} proveedor{{ producto.suppliers_count > 1 ? 'es' : '' }}
                                            </button>
                                        </div>
                                        <button
                                            v-else
                                            @click="abrirModalProveedores(producto)"
                                            class="inline-flex items-center px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg text-xs hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Agregar proveedor
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                            {{ formatMoney(producto.price) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="producto.has_inventory"
                                            :class="producto.stock > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold"
                                        >
                                            {{ producto.stock }}
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300"
                                        >
                                            Sin inventario
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex gap-2 justify-end">
                                            <button
                                                @click="abrirModalEditar(producto)"
                                                class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white text-sm font-medium rounded-lg transition-all"
                                                title="Editar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="eliminarProducto(producto.id)"
                                                class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-medium rounded-lg transition-all"
                                                title="Eliminar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="productos.last_page > 1" class="mt-6 flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Items por página:</span>
                            <select
                                v-model="itemsPorPagina"
                                @change="aplicarFiltros"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link
                                v-for="link in productos.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : link.url
                                        ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600'
                                        : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                                ]"
                                :disabled="!link.url"
                                preserve-state
                                preserve-scroll
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Nuevo Producto -->
        <div v-if="showModalNuevo" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModalNuevo = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form @submit.prevent="submitNuevo">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-5 pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-7 h-7 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Nuevo Producto
                                </h3>
                                <button type="button" @click="showModalNuevo = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nombre del producto *
                                    </label>
                                    <input
                                        v-model="formNuevo.name"
                                        type="text"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        required
                                    />
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Descripción
                                    </label>
                                    <textarea
                                        v-model="formNuevo.description"
                                        rows="2"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Código de barras
                                    </label>
                                    <input
                                        v-model="formNuevo.barcode"
                                        type="text"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Precio de venta *
                                    </label>
                                    <input
                                        v-model="formNuevo.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        required
                                    />
                                </div>
                            </div>

                            <!-- Sección de Categorías -->
                            <div class="col-span-2 mt-6">
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                                    <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        Categorías (Opcional)
                                    </label>
                                    <select
                                        v-model="formNuevo.category_ids"
                                        multiple
                                        size="5"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    >
                                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                            {{ categoria.icon }} {{ categoria.name }}
                                        </option>
                                    </select>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples categorías
                                    </p>
                                </div>
                            </div>

                            <!-- Sección de Proveedores -->
                            <div class="col-span-2 mt-4">
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                                    <div class="mb-3">
                                        <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            Proveedores (Opcional)
                                        </label>
                                        <select
                                            v-model="formNuevo.selected_suppliers"
                                            multiple
                                            size="5"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        >
                                            <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                                                {{ proveedor.name }}
                                            </option>
                                        </select>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                            Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples proveedores
                                        </p>
                                    </div>

                                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                        <div class="flex">
                                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="text-sm text-blue-800 dark:text-blue-300">
                                                <p class="font-semibold mb-1">¿No encuentras el proveedor?</p>
                                                <p>Primero debes agregarlo en la sección de <Link :href="route('proveedores.index')" class="font-semibold underline hover:text-blue-600">Proveedores</Link>. Después de crear el producto, podrás asignar costos y códigos específicos para cada proveedor.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="showModalNuevo = false"
                                class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="formNuevo.processing"
                                class="px-6 py-2 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50"
                            >
                                <span v-if="formNuevo.processing">Guardando...</span>
                                <span v-else>Guardar Producto</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Producto -->
        <div v-if="showModalEditar" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showModalEditar = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form @submit.prevent="submitEditar">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-5 pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Producto</h3>
                                <button type="button" @click="showModalEditar = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre del producto *</label>
                                    <input
                                        v-model="formEditar.name"
                                        type="text"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    />
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descripción</label>
                                    <textarea
                                        v-model="formEditar.description"
                                        rows="2"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código de barras</label>
                                    <input
                                        v-model="formEditar.barcode"
                                        type="text"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Precio de venta *</label>
                                    <input
                                        v-model="formEditar.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    />
                                </div>
                            </div>

                            <!-- Sección de Categorías -->
                            <div class="col-span-2 mt-6">
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                                    <label class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        Categorías (Opcional)
                                    </label>
                                    <select
                                        v-model="formEditar.category_ids"
                                        multiple
                                        size="5"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    >
                                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                            {{ categoria.icon }} {{ categoria.name }}
                                        </option>
                                    </select>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples categorías
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button type="button" @click="showModalEditar = false" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                            <button type="submit" :disabled="formEditar.processing" class="px-6 py-2 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50">
                                <span v-if="formEditar.processing">Guardando...</span>
                                <span v-else>Actualizar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Proveedores (continuará en el siguiente mensaje debido a límites) -->
         <!-- Modal Gestión de Proveedores -->
        <div v-if="showModalProveedores && productoSeleccionado" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showModalProveedores = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-white">Proveedores del Producto</h3>
                                <p class="text-indigo-100 text-sm mt-1">{{ productoSeleccionado.name }}</p>
                            </div>
                            <button type="button" @click="showModalProveedores = false" class="text-white hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 px-6 py-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Lista de Proveedores ({{ productoSeleccionado.suppliers_list.length }})
                            </h4>
                            <button
                                v-if="proveedoresDisponibles.length > 0"
                                @click="abrirModalAgregarProveedor"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-lg font-semibold transition-all"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Agregar Proveedor
                            </button>
                        </div>

                        <div v-if="productoSeleccionado.suppliers_list.length === 0" class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-lg font-medium text-gray-900 dark:text-white">No hay proveedores asignados</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Agrega proveedores para comparar precios</p>
                        </div>

                        <div v-else class="space-y-3 max-h-96 overflow-y-auto">
                            <div
                                v-for="proveedor in productoSeleccionado.suppliers_list"
                                :key="proveedor.id"
                                class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white">{{ proveedor.name }}</h5>
                                            <span
                                                v-if="proveedor.is_preferred"
                                                class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 rounded-full text-xs font-semibold"
                                            >
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                Preferido
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div>
                                                <span class="text-gray-600 dark:text-gray-400">Costo:</span>
                                                <span class="font-bold text-indigo-600 dark:text-indigo-400 ml-2">{{ formatMoney(proveedor.cost) }}</span>
                                            </div>
                                            <div v-if="proveedor.supplier_code">
                                                <span class="text-gray-600 dark:text-gray-400">Código:</span>
                                                <span class="font-medium text-gray-900 dark:text-white ml-2">{{ proveedor.supplier_code }}</span>
                                            </div>
                                        </div>
                                        <p v-if="proveedor.notes" class="text-xs text-gray-600 dark:text-gray-400 mt-2">{{ proveedor.notes }}</p>
                                    </div>
                                    <div class="flex gap-2 ml-4">
                                        <button
                                            @click="abrirModalEditarProveedor(proveedor)"
                                            class="p-2 text-yellow-600 hover:bg-yellow-100 dark:hover:bg-yellow-900/30 rounded-lg transition-colors"
                                            title="Editar"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="eliminarProveedor(proveedor.id)"
                                            class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                            title="Eliminar"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end">
                        <button @click="showModalProveedores = false" class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-lg font-semibold shadow-lg transition-all">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Agregar Proveedor -->
        <div v-if="showModalAgregarProveedor && productoSeleccionado" class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showModalAgregarProveedor = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitAgregarProveedor">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                            <h3 class="text-xl font-bold text-white">Agregar Proveedor</h3>
                        </div>

                        <div class="bg-white dark:bg-gray-800 px-6 py-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Proveedor *</label>
                                <select
                                    v-model="formAgregarProveedor.supplier_id"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                    required
                                >
                                    <option :value="null">Selecciona un proveedor</option>
                                    <option v-for="proveedor in proveedoresDisponibles" :key="proveedor.id" :value="proveedor.id">
                                        {{ proveedor.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Costo *</label>
                                <input
                                    v-model="formAgregarProveedor.cost"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código del proveedor</label>
                                <input
                                    v-model="formAgregarProveedor.supplier_code"
                                    type="text"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="SKU o código interno"
                                />
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input
                                        v-model="formAgregarProveedor.is_preferred"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5"
                                    />
                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Marcar como proveedor preferido</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notas</label>
                                <textarea
                                    v-model="formAgregarProveedor.notes"
                                    rows="2"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Tiempo de entrega, condiciones, etc."
                                ></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button type="button" @click="showModalAgregarProveedor = false" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                            <button type="submit" :disabled="formAgregarProveedor.processing" class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50">
                                <span v-if="formAgregarProveedor.processing">Guardando...</span>
                                <span v-else">Agregar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Proveedor -->
        <div v-if="showModalEditarProveedor && proveedorSeleccionado" class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showModalEditarProveedor = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitEditarProveedor">
                        <div class="bg-gradient-to-r from-yellow-500 to-amber-600 px-6 py-4">
                            <h3 class="text-xl font-bold text-white">Editar Proveedor</h3>
                            <p class="text-yellow-100 text-sm mt-1">{{ proveedorSeleccionado.name }}</p>
                        </div>

                        <div class="bg-white dark:bg-gray-800 px-6 py-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Costo *</label>
                                <input
                                    v-model="formEditarProveedor.cost"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código del proveedor</label>
                                <input
                                    v-model="formEditarProveedor.supplier_code"
                                    type="text"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white"
                                />
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input
                                        v-model="formEditarProveedor.is_preferred"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 h-5 w-5"
                                    />
                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Marcar como proveedor preferido</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notas</label>
                                <textarea
                                    v-model="formEditarProveedor.notes"
                                    rows="2"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white"
                                ></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button type="button" @click="showModalEditarProveedor = false" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                            <button type="submit" :disabled="formEditarProveedor.processing" class="px-6 py-2 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50">
                                <span v-if="formEditarProveedor.processing">Guardando...</span>
                                <span v-else>Actualizar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
