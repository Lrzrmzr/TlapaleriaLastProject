<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    faltantes: Array,
    productosStockBajo: Array,
    stats: Object,
    filters: Object,
    faltantesManualesHabilitados: Boolean,
});

// Estados para modales
const showModalNuevoFaltante = ref(false);
const showModalEditarFaltante = ref(false);
const showModalStockBajo = ref(false);
const faltanteSeleccionado = ref(null);

// Form para nuevo faltante
const formNuevo = useForm({
    descripcion: '',
    pedido: 'GENERAL',
    confirmado: false,
});

// Form para editar faltante
const formEditar = useForm({
    id: null,
    descripcion: '',
    pedido: 'GENERAL',
});

// Filtros
const filterConfirmado = ref(props.filters?.confirmado ?? null);
const filterPedido = ref(props.filters?.pedido ?? '');

// Selección múltiple
const selected = ref([]);

// Funciones
function submitNuevoFaltante() {
    formNuevo.post(route('faltantes.store'), {
        onSuccess: () => {
            formNuevo.reset();
            showModalNuevoFaltante.value = false;
        },
    });
}

function abrirEditarFaltante(faltante) {
    faltanteSeleccionado.value = faltante;
    formEditar.id = faltante.id;
    formEditar.descripcion = faltante.descripcion;
    formEditar.pedido = faltante.pedido;
    showModalEditarFaltante.value = true;
}

function submitEditarFaltante() {
    formEditar.put(route('faltantes.update', formEditar.id), {
        onSuccess: () => {
            formEditar.reset();
            showModalEditarFaltante.value = false;
            faltanteSeleccionado.value = null;
        },
    });
}

function eliminarFaltante(id) {
    if (confirm('¿Estás seguro de eliminar este faltante?')) {
        router.delete(route('faltantes.destroy', id));
    }
}

function aplicarFiltros() {
    router.get(route('faltantes.index'), {
        confirmado: filterConfirmado.value,
        pedido: filterPedido.value,
    });
}

function limpiarFiltros() {
    filterConfirmado.value = null;
    filterPedido.value = '';
    router.get(route('faltantes.index'));
}

function confirmarSeleccionados() {
    if (selected.value.length === 0) {
        alert('Selecciona al menos un faltante');
        return;
    }
    router.post(route('faltantes.confirmar'), { ids: selected.value }, {
        onSuccess: () => {
            selected.value = [];
        }
    });
}

function toggleSeleccion(id, confirmado) {
    if (confirmado) return; // No permitir seleccionar confirmados

    const index = selected.value.indexOf(id);
    if (index > -1) {
        selected.value.splice(index, 1);
    } else {
        selected.value.push(id);
    }
}

function seleccionarTodos() {
    const pendientes = props.faltantes.filter(f => !f.confirmado);
    if (selected.value.length === pendientes.length) {
        selected.value = [];
    } else {
        selected.value = pendientes.map(f => f.id);
    }
}

const faltantesPendientes = computed(() => props.faltantes.filter(f => !f.confirmado));
const todosPendientesSeleccionados = computed(() => {
    const pendientes = faltantesPendientes.value;
    return pendientes.length > 0 && selected.value.length === pendientes.length;
});

// Formatear moneda
function formatMoney(value) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
}
</script>

<template>
    <Head title="Faltantes - Ferretería TOTORO" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                        Gestión de Faltantes
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Productos faltantes y stock bajo (tabla provisional)
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="showModalStockBajo = true"
                        class="bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Stock Bajo ({{ stats.stockBajo }})
                    </button>
                    <button
                        v-if="faltantesManualesHabilitados"
                        @click="showModalNuevoFaltante = true"
                        class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Agregar Faltante
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-red-600 dark:text-red-400 uppercase">Total Faltantes</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.total }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-red-500 to-pink-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-orange-600 dark:text-orange-400 uppercase">Pendientes</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.pendientes }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-orange-500 to-amber-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 dark:text-green-400 uppercase">Confirmados</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.confirmados }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-emerald-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400 uppercase">Stock Bajo</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.stockBajo }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-yellow-500 to-amber-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros y Acciones -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                                <select
                                    v-model="filterConfirmado"
                                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                                >
                                    <option :value="null">Todos</option>
                                    <option :value="0">Pendientes</option>
                                    <option :value="1">Confirmados</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Pedido</label>
                                <select
                                    v-model="filterPedido"
                                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Todos</option>
                                    <option value="GENERAL">GENERAL</option>
                                    <option value="TRUPER">TRUPER</option>
                                </select>
                            </div>
                            <div class="flex gap-2 items-end">
                                <button
                                    @click="aplicarFiltros"
                                    class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-all"
                                >
                                    Aplicar
                                </button>
                                <button
                                    @click="limpiarFiltros"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-semibold transition-all"
                                >
                                    Limpiar
                                </button>
                            </div>
                        </div>
                        <div v-if="selected.length > 0" class="flex gap-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400 self-center">
                                {{ selected.length }} seleccionado(s)
                            </span>
                            <button
                                @click="confirmarSeleccionados"
                                class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-md"
                            >
                                Confirmar Selección
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Faltantes -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-7 h-7 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Lista de Faltantes
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-4 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="todosPendientesSeleccionados"
                                            @change="seleccionarTodos"
                                            :disabled="faltantesPendientes.length === 0"
                                            class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                        />
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Fecha/Hora
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Descripción
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Pedido
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="faltantes.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-lg font-medium">No hay faltantes registrados</p>
                                        <p class="text-sm mt-1">Agrega un nuevo faltante usando el botón de arriba</p>
                                    </td>
                                </tr>
                                <tr v-for="faltante in faltantes" :key="faltante.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            :checked="selected.includes(faltante.id)"
                                            @change="toggleSeleccion(faltante.id, faltante.confirmado)"
                                            :disabled="faltante.confirmado"
                                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 disabled:opacity-50"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ faltante.created_at }}</div>
                                            <div class="text-gray-500 dark:text-gray-400">{{ faltante.fecha }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            {{ faltante.descripcion }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="faltante.pedido === 'GENERAL'
                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                                                : 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                        >
                                            {{ faltante.pedido }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="faltante.confirmado"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Confirmado
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                            Pendiente
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ faltante.user }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex gap-2 justify-end">
                                            <button
                                                @click="abrirEditarFaltante(faltante)"
                                                class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white text-sm font-medium rounded-lg transition-all"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="eliminarFaltante(faltante.id)"
                                                class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-medium rounded-lg transition-all"
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
                </div>

            </div>
        </div>

        <!-- Modal Nuevo Faltante -->
        <div v-if="showModalNuevoFaltante" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModalNuevoFaltante = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitNuevoFaltante">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-5 pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-7 h-7 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Nuevo Faltante
                                </h3>
                                <button type="button" @click="showModalNuevoFaltante = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Descripción del producto faltante
                                    </label>
                                    <textarea
                                        v-model="formNuevo.descripcion"
                                        rows="3"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Ej: Clavos de 2 pulgadas, Cemento gris 50kg..."
                                        required
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Tipo de pedido
                                    </label>
                                    <select
                                        v-model="formNuevo.pedido"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="GENERAL">GENERAL</option>
                                        <option value="TRUPER">TRUPER</option>
                                    </select>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="formNuevo.confirmado"
                                        type="checkbox"
                                        id="confirmado"
                                        class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                    />
                                    <label for="confirmado" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Marcar como confirmado
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="showModalNuevoFaltante = false"
                                class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="formNuevo.processing"
                                class="px-6 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50"
                            >
                                <span v-if="formNuevo.processing">Guardando...</span>
                                <span v-else>Guardar Faltante</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Faltante -->
        <div v-if="showModalEditarFaltante" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModalEditarFaltante = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitEditarFaltante">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-5 pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-7 h-7 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar Faltante
                                </h3>
                                <button type="button" @click="showModalEditarFaltante = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Descripción del producto faltante
                                    </label>
                                    <textarea
                                        v-model="formEditar.descripcion"
                                        rows="3"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        required
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Tipo de pedido
                                    </label>
                                    <select
                                        v-model="formEditar.pedido"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="GENERAL">GENERAL</option>
                                        <option value="TRUPER">TRUPER</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="showModalEditarFaltante = false"
                                class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="formEditar.processing"
                                class="px-6 py-2 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50"
                            >
                                <span v-if="formEditar.processing">Guardando...</span>
                                <span v-else>Guardar Cambios</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Stock Bajo -->
        <div v-if="showModalStockBajo" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModalStockBajo = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-gradient-to-r from-yellow-500 to-amber-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-2xl font-bold text-white flex items-center">
                                <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Productos con Stock Bajo (≤ 2 unidades)
                            </h3>
                            <button type="button" @click="showModalStockBajo = false" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 px-6 py-6 max-h-96 overflow-y-auto">
                        <div v-if="productosStockBajo.length === 0" class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-lg font-medium text-gray-900 dark:text-white">¡Todo bien!</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">No hay productos con stock bajo</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="producto in productosStockBajo"
                                :key="producto.id"
                                class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                            {{ producto.name }}
                                        </h4>
                                        <div class="flex items-center gap-4 text-sm">
                                            <span class="flex items-center text-gray-600 dark:text-gray-400">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                                Stock: <strong :class="producto.stock === 0 ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400'" class="ml-1">
                                                    {{ producto.stock }} {{ producto.stock === 0 ? '(AGOTADO)' : 'unidades' }}
                                                </strong>
                                            </span>
                                            <span class="flex items-center text-gray-600 dark:text-gray-400">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Precio: <strong class="ml-1 text-gray-900 dark:text-white">{{ formatMoney(producto.price) }}</strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end">
                        <button
                            @click="showModalStockBajo = false"
                            class="px-6 py-2 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white rounded-lg font-semibold shadow-lg transition-all"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
