<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import StatsCard from '@/Components/Common/StatsCard.vue';
import SearchBar from '@/Components/Common/SearchBar.vue';
import PageHeader from '@/Components/Common/PageHeader.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';

const props = defineProps({
    proveedores: Array,
    stats: Object,
});

// Estados
const showModalNuevo = ref(false);
const showModalEditar = ref(false);
const proveedorSeleccionado = ref(null);
const busqueda = ref('');

// Forms
const formNuevo = useForm({
    name: '',
    contact_name: '',
    phone: '',
    email: '',
    address: '',
});

const formEditar = useForm({
    id: null,
    name: '',
    contact_name: '',
    phone: '',
    email: '',
    address: '',
});

// Computed
const proveedoresFiltrados = computed(() => {
    if (!busqueda.value) return props.proveedores;

    return props.proveedores.filter(p =>
        p.name.toLowerCase().includes(busqueda.value.toLowerCase()) ||
        (p.contact_name && p.contact_name.toLowerCase().includes(busqueda.value.toLowerCase())) ||
        (p.email && p.email.toLowerCase().includes(busqueda.value.toLowerCase()))
    );
});

// Funciones
function abrirModalNuevo() {
    formNuevo.reset();
    showModalNuevo.value = true;
}

function submitNuevo() {
    formNuevo.post(route('proveedores.store'), {
        onSuccess: () => {
            formNuevo.reset();
            showModalNuevo.value = false;
        },
    });
}

function abrirModalEditar(proveedor) {
    proveedorSeleccionado.value = proveedor;
    formEditar.id = proveedor.id;
    formEditar.name = proveedor.name;
    formEditar.contact_name = proveedor.contact_name;
    formEditar.phone = proveedor.phone;
    formEditar.email = proveedor.email;
    formEditar.address = proveedor.address;
    showModalEditar.value = true;
}

function submitEditar() {
    formEditar.put(route('proveedores.update', formEditar.id), {
        onSuccess: () => {
            formEditar.reset();
            showModalEditar.value = false;
            proveedorSeleccionado.value = null;
        },
    });
}

function eliminarProveedor(id) {
    if (confirm('¿Estás seguro de eliminar este proveedor?')) {
        router.delete(route('proveedores.destroy', id));
    }
}
</script>

<template>
    <Head title="Proveedores - Ferretería TOTORO" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Gestión de Proveedores"
                description="Administra tus proveedores y sus contactos"
                button-text="Nuevo Proveedor"
                @action="abrirModalNuevo"
            />
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <StatsCard
                        title="Total Proveedores"
                        :value="stats.total"
                        icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                        color-from="blue"
                    />
                    <StatsCard
                        title="Con Productos"
                        :value="stats.withProducts"
                        icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        color-from="green"
                    />
                    <StatsCard
                        title="Sin Productos"
                        :value="stats.withoutProducts"
                        icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        color-from="orange"
                    />
                </div>

                <!-- Búsqueda -->
                <SearchBar
                    v-model="busqueda"
                    label="Buscar proveedor:"
                    placeholder="Nombre, contacto o email..."
                    :total="proveedores.length"
                    :filtered="proveedoresFiltrados.length"
                    class="mb-6"
                />

                <!-- Tabla de Proveedores -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-7 h-7 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Listado de Proveedores
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Empresa</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Contacto</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Teléfono</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Productos</th>
                                    <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="proveedoresFiltrados.length === 0">
                                    <td colspan="6">
                                        <EmptyState
                                            icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                            title="No hay proveedores"
                                            description="Agrega proveedores usando el botón de arriba"
                                        />
                                    </td>
                                </tr>
                                <tr v-for="proveedor in proveedoresFiltrados" :key="proveedor.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ proveedor.name }}</div>
                                        <div v-if="proveedor.address" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ proveedor.address }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ proveedor.contact_name || 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ proveedor.phone || 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ proveedor.email || 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ proveedor.products_count }} producto{{ proveedor.products_count !== 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex gap-2 justify-end">
                                            <button
                                                @click="abrirModalEditar(proveedor)"
                                                class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white text-sm font-medium rounded-lg transition-all"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="eliminarProveedor(proveedor.id)"
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

        <!-- Modal Nuevo Proveedor -->
        <div v-if="showModalNuevo" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showModalNuevo = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitNuevo">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-5 pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Nuevo Proveedor</h3>
                                <button type="button" @click="showModalNuevo = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre de la empresa *</label>
                                    <input v-model="formNuevo.name" type="text" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre del contacto</label>
                                    <input v-model="formNuevo.contact_name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Teléfono</label>
                                        <input v-model="formNuevo.phone" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                                        <input v-model="formNuevo.email" type="email" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dirección</label>
                                    <textarea v-model="formNuevo.address" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button type="button" @click="showModalNuevo = false" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                            <button type="submit" :disabled="formNuevo.processing" class="px-6 py-2 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50">
                                <span v-if="formNuevo.processing">Guardando...</span>
                                <span v-else>Guardar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Proveedor -->
        <div v-if="showModalEditar" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showModalEditar = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitEditar">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-5 pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Proveedor</h3>
                                <button type="button" @click="showModalEditar = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre de la empresa *</label>
                                    <input v-model="formEditar.name" type="text" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre del contacto</label>
                                    <input v-model="formEditar.contact_name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Teléfono</label>
                                        <input v-model="formEditar.phone" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                                        <input v-model="formEditar.email" type="email" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dirección</label>
                                    <textarea v-model="formEditar.address" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white"></textarea>
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

    </AuthenticatedLayout>
</template>
