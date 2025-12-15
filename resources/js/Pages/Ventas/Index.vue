<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    ventas: Array,
    productos: Array,
    user: Object,
    ventasLibresHabilitadas: Boolean
});

// Tabs para ventas libres y catálogo
const activeTab = ref('libre');

// Form para venta libre
const formLibre = useForm({
    descripcion: '',
    total: 0,
});

// Form para venta de catálogo
const formCatalogo = useForm({
    items: [], // Array de { product_id, quantity, price }
});

// Carrito de compras para venta de catálogo
const carritoItems = ref([]);

// Modal states
const showModalLibre = ref(false);
const showModalCatalogo = ref(false);
const showModalDetalles = ref(false);
const ventaSeleccionada = ref(null);

// Función para enviar venta libre
function submitVentaLibre() {
    formLibre.post(route('ventas.store'), {
        onSuccess: () => {
            formLibre.reset();
            showModalLibre.value = false;
        },
    });
}

// Calcular totales
const totalVentasDia = computed(() => {
    return props.ventas.reduce((sum, venta) => sum + parseFloat(venta.total), 0);
});

const cantidadVentas = computed(() => props.ventas.length);

// Formatear moneda
function formatMoney(value) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
}

// Función para ver detalles de una venta
function verDetalles(venta) {
    ventaSeleccionada.value = venta;
    showModalDetalles.value = true;
}

// Función para cerrar modal de detalles
function cerrarDetalles() {
    showModalDetalles.value = false;
    ventaSeleccionada.value = null;
}

// Funciones para venta de catálogo
const productoSeleccionado = ref(null);
const cantidadSeleccionada = ref(1);
const busquedaProducto = ref('');
const mostrarSugerencias = ref(false);

function agregarAlCarrito() {
    if (!productoSeleccionado.value) return;

    const producto = props.productos.find(p => p.id === productoSeleccionado.value);
    if (!producto) return;

    // Verificar si ya está en el carrito
    const indexExistente = carritoItems.value.findIndex(item => item.product_id === producto.id);

    if (indexExistente !== -1) {
        // Actualizar cantidad si ya existe
        carritoItems.value[indexExistente].quantity += cantidadSeleccionada.value;
        carritoItems.value[indexExistente].subtotal = carritoItems.value[indexExistente].quantity * producto.price;
    } else {
        // Agregar nuevo item
        carritoItems.value.push({
            product_id: producto.id,
            name: producto.name,
            price: producto.price,
            quantity: cantidadSeleccionada.value,
            stock_disponible: producto.stock,
            subtotal: producto.price * cantidadSeleccionada.value
        });
    }

    // Reset
    limpiarBusqueda();
    cantidadSeleccionada.value = 1;
}

function eliminarDelCarrito(index) {
    carritoItems.value.splice(index, 1);
}

function actualizarCantidad(index, nuevaCantidad) {
    if (nuevaCantidad > 0 && nuevaCantidad <= carritoItems.value[index].stock_disponible) {
        carritoItems.value[index].quantity = nuevaCantidad;
        carritoItems.value[index].subtotal = carritoItems.value[index].price * nuevaCantidad;
    }
}

const totalCarrito = computed(() => {
    return carritoItems.value.reduce((sum, item) => sum + item.subtotal, 0);
});

const productosDisponibles = computed(() => {
    return props.productos.filter(p => p.stock > 0);
});

const productosFiltrados = computed(() => {
    if (!busquedaProducto.value || busquedaProducto.value.length < 2) {
        return [];
    }

    const termino = busquedaProducto.value.toLowerCase();
    return productosDisponibles.value
        .filter(p =>
            p.name.toLowerCase().includes(termino) ||
            (p.barcode && p.barcode.toLowerCase().includes(termino))
        )
        .slice(0, 10); // Limitar a 10 resultados
});

function seleccionarProducto(producto) {
    productoSeleccionado.value = producto.id;
    busquedaProducto.value = producto.name;
    mostrarSugerencias.value = false;
}

function limpiarBusqueda() {
    busquedaProducto.value = '';
    productoSeleccionado.value = null;
    mostrarSugerencias.value = false;
}

function submitVentaCatalogo() {
    if (carritoItems.value.length === 0) {
        alert('Agrega al menos un producto al carrito');
        return;
    }

    formCatalogo.items = carritoItems.value.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        price: item.price,
        subtotal: item.subtotal
    }));

    formCatalogo.post(route('ventas.store'), {
        onSuccess: () => {
            formCatalogo.reset();
            carritoItems.value = [];
            showModalCatalogo.value = false;
        },
    });
}

function abrirModalCatalogo() {
    carritoItems.value = [];
    limpiarBusqueda();
    cantidadSeleccionada.value = 1;
    showModalCatalogo.value = true;
}
</script>

<template>
    <Head title="Ventas - Ferretería TOTORO" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                        Gestión de Ventas
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Registra ventas libres o del catálogo de productos
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        v-if="ventasLibresHabilitadas"
                        @click="showModalLibre = true"
                        class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nueva Venta Libre
                    </button>
                    <button
                        @click="abrirModalCatalogo"
                        class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Venta de Catálogo
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 dark:text-green-400 uppercase">Total Ventas Hoy</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ formatMoney(totalVentasDia) }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-emerald-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400 uppercase">Cantidad de Ventas</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ cantidadVentas }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-500 to-cyan-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-orange-600 dark:text-orange-400 uppercase">Promedio por Venta</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ formatMoney(cantidadVentas > 0 ? totalVentasDia / cantidadVentas : 0) }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-orange-500 to-amber-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Ventas -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-7 h-7 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Ventas del Día
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Hora
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Detalles
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Vendedor
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="ventas.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-lg font-medium">No hay ventas registradas hoy</p>
                                        <p class="text-sm mt-1">Agrega tu primera venta usando los botones de arriba</p>
                                    </td>
                                </tr>
                                <tr v-for="venta in ventas" :key="venta.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ venta.created_at }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            {{ venta.customer }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div v-for="(detail, index) in venta.details" :key="index" class="flex items-center gap-2">
                                                <span v-if="detail.tipo_venta === 'libre'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                                    Libre
                                                </span>
                                                <span v-else class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    Catálogo
                                                </span>
                                                <span class="text-sm text-gray-900 dark:text-white">
                                                    {{ detail.descripcion }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    (x{{ detail.quantity }})
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                            {{ formatMoney(venta.total) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ venta.user }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button
                                            @click="verDetalles(venta)"
                                            class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-sm font-medium rounded-lg transition-all duration-300 shadow-md hover:shadow-lg"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Venta Libre -->
        <div v-if="showModalLibre" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModalLibre = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitVentaLibre">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-5 pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-7 h-7 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Nueva Venta Libre
                                </h3>
                                <button type="button" @click="showModalLibre = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Descripción de la venta
                                    </label>
                                    <textarea
                                        v-model="formLibre.descripcion"
                                        rows="3"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Ej: Clavos, tornillos, cemento..."
                                        required
                                    ></textarea>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Describe los productos o servicios vendidos
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Total de la venta
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 dark:text-gray-400">
                                            $
                                        </span>
                                        <input
                                            v-model="formLibre.total"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg pl-8 pr-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                            placeholder="0.00"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="showModalLibre = false"
                                class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="formLibre.processing"
                                class="px-6 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50"
                            >
                                <span v-if="formLibre.processing">Guardando...</span>
                                <span v-else>Guardar Venta</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Venta Catálogo -->
        <div v-if="showModalCatalogo" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModalCatalogo = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form @submit.prevent="submitVentaCatalogo">
                        <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold text-white flex items-center">
                                    <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Venta de Catálogo
                                </h3>
                                <button type="button" @click="showModalCatalogo = false" class="text-white hover:text-gray-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 px-6 py-6">
                            <!-- Sección Agregar Producto -->
                            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Agregar Producto
                                </h4>

                                <div v-if="productosDisponibles.length === 0" class="text-center py-8">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        No hay productos con stock disponible
                                    </p>
                                </div>

                                <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2 relative">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Buscar Producto *
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="busquedaProducto"
                                                @focus="mostrarSugerencias = true"
                                                @input="mostrarSugerencias = true"
                                                type="text"
                                                placeholder="Escribe para buscar por nombre o código de barras..."
                                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 pr-10 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                            />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg v-if="!productoSeleccionado" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <button
                                                    v-else
                                                    type="button"
                                                    @click="limpiarBusqueda"
                                                    class="text-gray-400 hover:text-gray-600"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Sugerencias de autocompletado -->
                                        <div
                                            v-if="mostrarSugerencias && productosFiltrados.length > 0 && !productoSeleccionado"
                                            class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-64 overflow-y-auto"
                                        >
                                            <button
                                                v-for="producto in productosFiltrados"
                                                :key="producto.id"
                                                type="button"
                                                @click="seleccionarProducto(producto)"
                                                class="w-full text-left px-4 py-3 hover:bg-blue-50 dark:hover:bg-gray-600 border-b border-gray-200 dark:border-gray-600 last:border-0 transition-colors"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <p class="font-semibold text-gray-900 dark:text-white">{{ producto.name }}</p>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                                            {{ formatMoney(producto.price) }}
                                                        </p>
                                                    </div>
                                                    <div class="ml-4">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                                              :class="producto.stock > 10 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'">
                                                            Stock: {{ producto.stock }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>

                                        <!-- Mensaje cuando no hay resultados -->
                                        <div
                                            v-if="mostrarSugerencias && busquedaProducto.length >= 2 && productosFiltrados.length === 0 && !productoSeleccionado"
                                            class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg px-4 py-3"
                                        >
                                            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                                                No se encontraron productos con "{{ busquedaProducto }}"
                                            </p>
                                        </div>

                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Escribe al menos 2 caracteres para buscar
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Cantidad *
                                        </label>
                                        <div class="flex gap-2">
                                            <input
                                                v-model.number="cantidadSeleccionada"
                                                type="number"
                                                min="1"
                                                :max="productoSeleccionado ? productos.find(p => p.id === productoSeleccionado)?.stock : 999"
                                                class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                                :disabled="!productoSeleccionado"
                                            />
                                            <button
                                                type="button"
                                                @click="agregarAlCarrito"
                                                :disabled="!productoSeleccionado"
                                                class="px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg"
                                                title="Agregar al carrito"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                        <p v-if="productoSeleccionado" class="mt-1 text-xs text-green-600 dark:text-green-400">
                                            Stock disponible: {{ productos.find(p => p.id === productoSeleccionado)?.stock }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Carrito -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Carrito de Compra ({{ carritoItems.length }} items)
                                </h4>

                                <div v-if="carritoItems.length === 0" class="text-center py-12 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">El carrito está vacío</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Agrega productos para realizar la venta</p>
                                </div>

                                <div v-else class="space-y-3 max-h-96 overflow-y-auto">
                                    <div
                                        v-for="(item, index) in carritoItems"
                                        :key="index"
                                        class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 flex items-center justify-between"
                                    >
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ item.name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ formatMoney(item.price) }} x
                                                <input
                                                    type="number"
                                                    :value="item.quantity"
                                                    @input="actualizarCantidad(index, parseInt($event.target.value))"
                                                    min="1"
                                                    :max="item.stock_disponible"
                                                    class="w-16 inline-block border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-center dark:bg-gray-700 dark:text-white"
                                                />
                                                = {{ formatMoney(item.subtotal) }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Stock disponible: {{ item.stock_disponible }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            @click="eliminarDelCarrito(index)"
                                            class="ml-4 p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Total -->
                                <div v-if="carritoItems.length > 0" class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl p-4">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">Total:</span>
                                        <span class="text-3xl font-bold text-blue-700 dark:text-blue-300">
                                            {{ formatMoney(totalCarrito) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="showModalCatalogo = false"
                                class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="formCatalogo.processing || carritoItems.length === 0"
                                class="px-6 py-2 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50"
                            >
                                <span v-if="formCatalogo.processing">Procesando...</span>
                                <span v-else>Completar Venta ({{ formatMoney(totalCarrito) }})</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Detalles de Venta -->
        <div v-if="showModalDetalles && ventaSeleccionada" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="cerrarDetalles"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-2xl font-bold text-white flex items-center">
                                <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Detalles de Venta #{{ ventaSeleccionada.id }}
                            </h3>
                            <button type="button" @click="cerrarDetalles" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 px-6 py-6">
                        <!-- Información General -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Hora</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ ventaSeleccionada.created_at }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Cliente</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ ventaSeleccionada.customer }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Vendedor</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ ventaSeleccionada.user }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4">
                                <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase mb-1">Total</p>
                                <p class="text-xl font-bold text-green-700 dark:text-green-300">{{ formatMoney(ventaSeleccionada.total) }}</p>
                            </div>
                        </div>

                        <!-- Detalles de Productos/Items -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                Items de la Venta
                            </h4>
                            <div class="space-y-3">
                                <div
                                    v-for="(detail, index) in ventaSeleccionada.details"
                                    :key="index"
                                    class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 hover:shadow-md transition-shadow"
                                >
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span
                                                    v-if="detail.tipo_venta === 'libre'"
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300"
                                                >
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                    </svg>
                                                    Venta Libre
                                                </span>
                                                <span
                                                    v-else
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                                                >
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                                    </svg>
                                                    Catálogo
                                                </span>
                                            </div>
                                            <p class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                                {{ detail.descripcion }}
                                            </p>
                                            <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                                    </svg>
                                                    Cantidad: <strong class="ml-1 text-gray-900 dark:text-white">{{ detail.quantity }}</strong>
                                                </span>
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Precio Unit: <strong class="ml-1 text-gray-900 dark:text-white">{{ formatMoney(detail.price) }}</strong>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Subtotal</p>
                                            <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                                {{ formatMoney(detail.subtotal) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total General -->
                        <div class="border-t border-gray-200 dark:border-gray-700 mt-6 pt-6">
                            <div class="flex items-center justify-between bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4">
                                <span class="text-lg font-bold text-gray-900 dark:text-white">Total de la Venta</span>
                                <span class="text-3xl font-bold text-green-700 dark:text-green-300">
                                    {{ formatMoney(ventaSeleccionada.total) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end">
                        <button
                            @click="cerrarDetalles"
                            class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-lg font-semibold shadow-lg transition-all"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
