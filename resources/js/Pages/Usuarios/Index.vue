<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    usuarios: Array,
    roles: Array,
    branches: Array,
    user: Object,
    stats: Object,
});

// Estados
const showModalDetalles = ref(false);
const usuarioSeleccionado = ref(null);
const filtroRol = ref('');

// Computed
const usuariosFiltrados = computed(() => {
    if (!filtroRol.value) return props.usuarios;
    if (filtroRol.value === 'sin_rol') {
        return props.usuarios.filter(u => !u.role);
    }
    return props.usuarios.filter(u => u.role?.id === parseInt(filtroRol.value));
});

const esAdmin = computed(() => {
    return props.user.id === 1 || props.user.role?.name === 'administrador';
});

// Funciones
function asignarRol(usuarioId, rolId) {
    router.post(route('usuarios.asignarRol'), {
        usuario_id: usuarioId,
        rol_id: rolId || null
    });
}

function asignarSucursal(usuarioId, branchId) {
    router.post(route('usuarios.asignarSucursal', usuarioId), {
        branch_id: branchId || null
    });
}

function verDetalles(usuario) {
    usuarioSeleccionado.value = usuario;
    showModalDetalles.value = true;
}

function cerrarDetalles() {
    showModalDetalles.value = false;
    usuarioSeleccionado.value = null;
}

function getRolColor(rolName) {
    const colors = {
        'administrador': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'vendedor': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'gerente': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    };
    return colors[rolName?.toLowerCase()] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}
</script>

<template>
    <Head title="Usuarios - Ferretería TOTORO" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                        Gestión de Usuarios
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Administra usuarios y asigna roles del sistema
                    </p>
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
                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400 uppercase">Total Usuarios</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.total }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-500 to-cyan-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 dark:text-green-400 uppercase">Con Rol Asignado</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.conRol }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-emerald-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-orange-600 dark:text-orange-400 uppercase">Sin Rol</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.sinRol }}
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
                                <p class="text-sm font-medium text-purple-600 dark:text-purple-400 uppercase">Verificados</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                    {{ stats.verificados }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-purple-500 to-pink-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por rol:</label>
                        <select
                            v-model="filtroRol"
                            class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Todos los usuarios</option>
                            <option value="sin_rol">Sin rol asignado</option>
                            <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.name }}</option>
                        </select>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Mostrando {{ usuariosFiltrados.length }} de {{ usuarios.length }} usuarios
                        </span>
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-7 h-7 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Lista de Usuarios
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Rol Actual
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Sucursal
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Asignar Rol
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Asignar Sucursal
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="usuariosFiltrados.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <p class="text-lg font-medium">No hay usuarios con este filtro</p>
                                    </td>
                                </tr>
                                <tr v-for="usuario in usuariosFiltrados" :key="usuario.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                                <span class="text-white font-bold text-lg">{{ usuario.name.charAt(0).toUpperCase() }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ usuario.name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ usuario.email }}</span>
                                            <svg v-if="usuario.email_verified" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" title="Email verificado">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="usuario.role"
                                            :class="getRolColor(usuario.role.name)"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                        >
                                            {{ usuario.role.name }}
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400"
                                        >
                                            Sin rol
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="usuario.branch"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            {{ usuario.branch.name }}
                                        </span>
                                        <span
                                            v-else-if="usuario.role?.id === 1"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Todas las sucursales
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300"
                                        >
                                            Sin sucursal
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select
                                            v-if="esAdmin"
                                            @change="asignarRol(usuario.id, $event.target.value)"
                                            :value="usuario.role?.id ?? ''"
                                            class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        >
                                            <option value="">Sin rol</option>
                                            <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.name }}</option>
                                        </select>
                                        <span v-else class="text-sm text-gray-400 dark:text-gray-500">
                                            Solo admin
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select
                                            v-if="esAdmin && usuario.role?.id !== 1"
                                            @change="asignarSucursal(usuario.id, $event.target.value)"
                                            :value="usuario.branch?.id ?? ''"
                                            class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white"
                                        >
                                            <option value="">Sin sucursal</option>
                                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                                        </select>
                                        <span v-else-if="usuario.role?.id === 1" class="text-sm text-purple-600 dark:text-purple-400 font-medium">
                                            No requiere
                                        </span>
                                        <span v-else class="text-sm text-gray-400 dark:text-gray-500">
                                            Solo admin
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button
                                            @click="verDetalles(usuario)"
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

        <!-- Modal Detalles de Usuario -->
        <div v-if="showModalDetalles && usuarioSeleccionado" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="cerrarDetalles"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-2xl font-bold text-white flex items-center">
                                <div class="h-12 w-12 bg-white rounded-full flex items-center justify-center mr-3">
                                    <span class="text-indigo-600 font-bold text-xl">{{ usuarioSeleccionado.name.charAt(0).toUpperCase() }}</span>
                                </div>
                                Detalles de Usuario
                            </h3>
                            <button type="button" @click="cerrarDetalles" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 px-6 py-6">
                        <div class="space-y-6">
                            <!-- Información Personal -->
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Información Personal
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Nombre</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ usuarioSeleccionado.name }}</p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Email</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ usuarioSeleccionado.email }}</p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Rol</p>
                                        <span
                                            v-if="usuarioSeleccionado.role"
                                            :class="getRolColor(usuarioSeleccionado.role.name)"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                        >
                                            {{ usuarioSeleccionado.role.name }}
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                        >
                                            Sin rol asignado
                                        </span>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Sucursal</p>
                                        <span
                                            v-if="usuarioSeleccionado.branch"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            {{ usuarioSeleccionado.branch.name }}
                                        </span>
                                        <span
                                            v-else-if="usuarioSeleccionado.role?.id === 1"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300"
                                        >
                                            Todas
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300"
                                        >
                                            Sin asignar
                                        </span>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 col-span-2">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Registrado</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ usuarioSeleccionado.created_at }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado de Verificación -->
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Estado de Verificación
                                </h4>
                                <div
                                    :class="usuarioSeleccionado.email_verified
                                        ? 'bg-green-50 dark:bg-green-900/20 border-green-500'
                                        : 'bg-red-50 dark:bg-red-900/20 border-red-500'"
                                    class="border-l-4 rounded-lg p-4"
                                >
                                    <div class="flex items-center">
                                        <svg
                                            v-if="usuarioSeleccionado.email_verified"
                                            class="w-6 h-6 text-green-500 mr-3"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <svg
                                            v-else
                                            class="w-6 h-6 text-red-500 mr-3"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ usuarioSeleccionado.email_verified ? 'Email Verificado' : 'Email No Verificado' }}
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                {{ usuarioSeleccionado.email_verified
                                                    ? 'Este usuario ha confirmado su dirección de correo'
                                                    : 'Este usuario aún no ha verificado su correo electrónico'
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
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
