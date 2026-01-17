<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    branches: Array,
});

// Estados
const showModal = ref(false);
const editingBranch = ref(null);
const busqueda = ref('');

// Form
const form = useForm({
    name: '',
    code: '',
    address: '',
    phone: '',
    email: '',
    manager_name: '',
    active: true,
    is_main: false,
    notes: '',
});

// Computed
const branchesFiltradas = computed(() => {
    if (!busqueda.value) return props.branches;

    return props.branches.filter(branch =>
        branch.name.toLowerCase().includes(busqueda.value.toLowerCase()) ||
        branch.code.toLowerCase().includes(busqueda.value.toLowerCase()) ||
        (branch.manager_name && branch.manager_name.toLowerCase().includes(busqueda.value.toLowerCase()))
    );
});

// Funciones
function openModal(branch = null) {
    if (branch) {
        editingBranch.value = branch;
        form.name = branch.name;
        form.code = branch.code;
        form.address = branch.address || '';
        form.phone = branch.phone || '';
        form.email = branch.email || '';
        form.manager_name = branch.manager_name || '';
        form.active = branch.active;
        form.is_main = branch.is_main;
        form.notes = branch.notes || '';
    } else {
        editingBranch.value = null;
        form.reset();
    }
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingBranch.value = null;
    form.reset();
}

function submit() {
    if (editingBranch.value) {
        form.put(route('sucursales.update', editingBranch.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('sucursales.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function deleteBranch(branch) {
    if (branch.is_main) {
        alert('No se puede eliminar la sucursal principal');
        return;
    }

    if (confirm(`¿Estás seguro de desactivar la sucursal "${branch.name}"? Los usuarios deberán ser reasignados.`)) {
        form.delete(route('sucursales.destroy', branch.id));
    }
}

function toggleStatus(branch) {
    if (branch.is_main && branch.active) {
        alert('No se puede desactivar la sucursal principal');
        return;
    }

    form.post(route('sucursales.toggle-status', branch.id));
}
</script>

<template>
    <Head title="Sucursales - Ferretería TOTORO" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                        Gestión de Sucursales
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Administra las sucursales del sistema
                    </p>
                </div>
                <button
                    @click="openModal()"
                    class="bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Sucursal
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Búsqueda -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Buscar sucursal:</label>
                            <input
                                v-model="busqueda"
                                type="text"
                                placeholder="Nombre, código o encargado..."
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-6">
                            Mostrando {{ branchesFiltradas.length }} de {{ branches.length }} sucursales
                        </div>
                    </div>
                </div>

                <!-- Grid de Sucursales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="branch in branchesFiltradas"
                        :key="branch.id"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300"
                        :class="[
                            branch.is_main ? 'ring-2 ring-yellow-400' : '',
                            !branch.active ? 'opacity-60' : ''
                        ]"
                    >
                        <!-- Header con badge -->
                        <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-white mb-1">
                                        {{ branch.name }}
                                    </h3>
                                    <p class="text-blue-100 text-sm font-semibold">
                                        Código: {{ branch.code }}
                                    </p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <span
                                        v-if="branch.is_main"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-400 text-gray-900"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        PRINCIPAL
                                    </span>
                                    <span
                                        :class="branch.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                    >
                                        {{ branch.active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="p-6">
                            <!-- Información -->
                            <div class="space-y-3 mb-4">
                                <div v-if="branch.address" class="flex items-start gap-2 text-sm">
                                    <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">{{ branch.address }}</span>
                                </div>

                                <div v-if="branch.phone" class="flex items-center gap-2 text-sm">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">{{ branch.phone }}</span>
                                </div>

                                <div v-if="branch.email" class="flex items-center gap-2 text-sm">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">{{ branch.email }}</span>
                                </div>

                                <div v-if="branch.manager_name" class="flex items-center gap-2 text-sm">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">
                                        <span class="font-semibold">Encargado:</span> {{ branch.manager_name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-3 mb-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Usuarios</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ branch.users_count }}</p>
                                </div>
                                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                                    <p class="text-xs text-green-600 dark:text-green-400 font-medium">Productos</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ branch.inventory_count }}</p>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex gap-2">
                                <button
                                    @click="openModal(branch)"
                                    class="flex-1 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white px-4 py-2 rounded-lg font-semibold transition-all text-sm"
                                >
                                    Editar
                                </button>
                                <button
                                    @click="toggleStatus(branch)"
                                    :disabled="branch.is_main && branch.active"
                                    class="flex-1 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white px-4 py-2 rounded-lg font-semibold transition-all text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ branch.active ? 'Desactivar' : 'Activar' }}
                                </button>
                                <button
                                    v-if="!branch.is_main"
                                    @click="deleteBranch(branch)"
                                    class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-2 rounded-lg font-semibold transition-all text-sm"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Notas -->
                            <div v-if="branch.notes" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-xs text-gray-500 dark:text-gray-400 italic">{{ branch.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay sucursales -->
                <div v-if="branchesFiltradas.length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p class="text-lg font-medium text-gray-900 dark:text-white">No hay sucursales</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Crea una nueva sucursal para comenzar</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <form @submit.prevent="submit">
                        <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold text-white">
                                    {{ editingBranch ? 'Editar Sucursal' : 'Nueva Sucursal' }}
                                </h3>
                                <button type="button" @click="closeModal" class="text-white hover:text-gray-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 px-6 py-6">
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Nombre -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nombre de la Sucursal *
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Ej: Ferretería TOTORO - Sucursal Norte"
                                    />
                                </div>

                                <!-- Código -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Código *
                                    </label>
                                    <input
                                        v-model="form.code"
                                        type="text"
                                        required
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white uppercase"
                                        placeholder="NORTE"
                                    />
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Teléfono
                                    </label>
                                    <input
                                        v-model="form.phone"
                                        type="text"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="555-1234"
                                    />
                                </div>

                                <!-- Email -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Email
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="sucursal@tlapaleria.com"
                                    />
                                </div>

                                <!-- Dirección -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Dirección
                                    </label>
                                    <textarea
                                        v-model="form.address"
                                        rows="2"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Calle Principal #123, Colonia..."
                                    ></textarea>
                                </div>

                                <!-- Encargado -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nombre del Encargado
                                    </label>
                                    <input
                                        v-model="form.manager_name"
                                        type="text"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Juan Pérez"
                                    />
                                </div>

                                <!-- Notas -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Notas
                                    </label>
                                    <textarea
                                        v-model="form.notes"
                                        rows="2"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Notas adicionales..."
                                    ></textarea>
                                </div>

                                <!-- Checkboxes -->
                                <div class="col-span-2 space-y-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.active"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5"
                                        />
                                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal Activa</span>
                                    </label>

                                    <label class="flex items-center">
                                        <input
                                            v-model="form.is_main"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 h-5 w-5"
                                        />
                                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Marcar como Sucursal Principal
                                            <span class="text-xs text-gray-500">(solo puede haber una)</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50"
                            >
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>{{ editingBranch ? 'Actualizar' : 'Guardar' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
