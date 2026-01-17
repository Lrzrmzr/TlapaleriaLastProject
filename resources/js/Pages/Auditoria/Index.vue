<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    activities: Object,
    users: Array,
    branches: Array,
    models: Object,
    filters: Object,
});

const page = usePage();

// Filtros (inicializados desde el servidor)
const busqueda = ref(props.filters?.search || '');
const filtros = ref({
    user_id: props.filters?.user_id || '',
    model: props.filters?.model || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    action: props.filters?.action || '',
    branch_id: props.filters?.branch_id || '',
});
const itemsPorPagina = ref(props.filters?.per_page || 10);

// Aplicar filtros con debounce
let filtroTimeout = null;
const aplicarFiltros = () => {
    clearTimeout(filtroTimeout);
    filtroTimeout = setTimeout(() => {
        const params = {
            search: busqueda.value || undefined,
            ...filtros.value,
            per_page: itemsPorPagina.value,
        };

        // Limpiar parámetros vacíos
        Object.keys(params).forEach(key => !params[key] && delete params[key]);

        router.get(route('auditoria.index'), params, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

// Limpiar filtros
const limpiarFiltros = () => {
    busqueda.value = '';
    filtros.value = {
        user_id: '',
        model: '',
        date_from: '',
        date_to: '',
        action: '',
        branch_id: '',
    };
    aplicarFiltros();
};

// Watchers para aplicar filtros automáticamente
watch([busqueda, filtros], () => {
    aplicarFiltros();
}, { deep: true });

// Obtener badge color según la acción
const getBadgeColor = (action) => {
    const colors = {
        'created': 'bg-green-100 text-green-800',
        'updated': 'bg-blue-100 text-blue-800',
        'deleted': 'bg-red-100 text-red-800',
        'restored': 'bg-purple-100 text-purple-800',
    };
    return colors[action] || 'bg-gray-100 text-gray-800';
};

// Formatear cambios
const formatearCambios = (properties) => {
    if (!properties || !properties.attributes) return null;

    const cambios = [];
    const attributes = properties.attributes || {};
    const old = properties.old || {};

    for (const key in attributes) {
        if (old[key] !== undefined && old[key] !== attributes[key]) {
            cambios.push({
                campo: key,
                anterior: old[key],
                nuevo: attributes[key]
            });
        }
    }

    return cambios.length > 0 ? cambios : null;
};
</script>

<template>
    <Head title="Auditoría del Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">
                    📊 Auditoría del Sistema
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Panel de Filtros -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Filtros</h3>

                    <!-- Campo de Búsqueda General -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Búsqueda General</label>
                        <input
                            v-model="busqueda"
                            type="text"
                            placeholder="Buscar por ID, descripción o usuario..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                        <p class="text-xs text-gray-500 mt-1">Busca por ID del log, descripción o nombre de usuario</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Filtro por Usuario -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                            <select
                                v-model="filtros.user_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                                <option value="">Todos los usuarios</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Filtro por Modelo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Registro</label>
                            <select
                                v-model="filtros.model"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                                <option value="">Todos los tipos</option>
                                <option v-for="(label, model) in models" :key="model" :value="model">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Filtro por Acción -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Acción</label>
                            <select
                                v-model="filtros.action"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                                <option value="">Todas las acciones</option>
                                <option value="created">Creado</option>
                                <option value="updated">Actualizado</option>
                                <option value="deleted">Eliminado</option>
                                <option value="restored">Restaurado</option>
                            </select>
                        </div>

                        <!-- Filtro por Fecha Desde -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                            <input
                                type="date"
                                v-model="filtros.date_from"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Filtro por Fecha Hasta -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                            <input
                                type="date"
                                v-model="filtros.date_to"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Filtro por Sucursal (solo para root) -->
                        <div v-if="branches.length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sucursal</label>
                            <select
                                v-model="filtros.branch_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                                <option value="">Todas las sucursales</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                    {{ branch.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-between mt-4">
                        <button
                            @click="limpiarFiltros"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all duration-200"
                        >
                            Limpiar Filtros
                        </button>
                        <div class="text-sm text-gray-600">
                            Mostrando {{ activities.from || 0 }} - {{ activities.to || 0 }} de {{ activities.total || 0 }} registros
                        </div>
                    </div>
                </div>

                <!-- Tabla de Actividades -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-purple-50 to-purple-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                        Acción
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                        Modelo
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                        Cambios
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-purple-900 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="activity in activities.data"
                                    :key="activity.id"
                                    class="hover:bg-purple-50 transition-colors duration-150"
                                >
                                    <!-- Usuario -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center rounded-full bg-purple-100 text-purple-600 font-semibold">
                                                {{ activity.causer_name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ activity.causer_name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Acción -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="getBadgeColor(activity.description)"
                                        >
                                            {{ activity.action_translated }}
                                        </span>
                                    </td>

                                    <!-- Modelo -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-medium">
                                            {{ activity.model_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            ID: {{ activity.subject_id }}
                                        </div>
                                    </td>

                                    <!-- Cambios -->
                                    <td class="px-6 py-4">
                                        <div v-if="formatearCambios(activity.properties)" class="text-sm">
                                            <div
                                                v-for="(cambio, index) in formatearCambios(activity.properties)"
                                                :key="index"
                                                class="mb-1"
                                            >
                                                <span class="font-medium text-gray-700">{{ cambio.campo }}:</span>
                                                <span class="text-red-600 line-through mx-1">{{ cambio.anterior }}</span>
                                                →
                                                <span class="text-green-600 mx-1">{{ cambio.nuevo }}</span>
                                            </div>
                                        </div>
                                        <div v-else class="text-sm text-gray-500">
                                            {{ activity.description === 'created' ? 'Registro creado' : 'Sin cambios' }}
                                        </div>
                                    </td>

                                    <!-- Fecha -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ activity.created_at }}</div>
                                        <div class="text-xs text-gray-400">
                                            {{ activity.created_at_human }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mensaje si no hay resultados -->
                    <div v-if="activities.data.length === 0" class="text-center py-12">
                        <div class="text-gray-400 text-lg">
                            📭 No se encontraron registros de auditoría
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="activities.last_page > 1" class="mt-6 flex items-center justify-between px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-700">Registros por página:</span>
                            <select
                                v-model="itemsPorPagina"
                                @change="aplicarFiltros"
                                class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-purple-500"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link
                                v-for="link in activities.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                    link.active
                                        ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white'
                                        : link.url
                                        ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
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
    </AuthenticatedLayout>
</template>
