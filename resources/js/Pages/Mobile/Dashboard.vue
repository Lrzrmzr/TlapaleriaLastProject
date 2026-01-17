<script setup>
import { ref, onMounted } from 'vue';
import { useMobileAuth } from '@/composables/useMobileAuth';
import { branchService } from '@/services';
import { Head } from '@inertiajs/vue3';

const { user, userBranch } = useMobileAuth();

const stats = ref(null);
const loading = ref(true);

const loadStats = async () => {
    try {
        loading.value = true;
        if (userBranch.value) {
            const response = await branchService.getStats(userBranch.value.id);
            stats.value = response.data.stats;
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadStats();
});

const navigateTo = (route) => {
    window.location.href = route;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Head title="Dashboard" />

        <!-- Header Móvil -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Ferretería TOTORO</h1>
                    <p class="text-blue-100 text-sm mt-1">{{ user?.name }}</p>
                    <p v-if="userBranch" class="text-blue-200 text-xs mt-0.5">
                        {{ userBranch.name }}
                    </p>
                </div>
                <button
                    @click="navigateTo('/logout')"
                    class="p-2 rounded-full bg-white/20 hover:bg-white/30"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Estadísticas Cards -->
        <div class="px-4 py-6 space-y-4">
            <!-- Loading -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <svg class="animate-spin h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Stats Cards -->
            <div v-else-if="stats" class="grid grid-cols-2 gap-4">
                <!-- Ventas Hoy -->
                <div class="bg-white rounded-xl shadow-md p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Ventas Hoy</p>
                            <p class="text-2xl font-bold text-green-600">
                                ${{ stats.ventas_hoy?.toLocaleString() || 0 }}
                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Gastos del Mes -->
                <div class="bg-white rounded-xl shadow-md p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Gastos Mes</p>
                            <p class="text-2xl font-bold text-red-600">
                                ${{ stats.gastos_mes?.toLocaleString() || 0 }}
                            </p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Stock Bajo -->
                <div class="bg-white rounded-xl shadow-md p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Stock Bajo</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                {{ stats.stock_bajo || 0 }}
                            </p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Faltantes -->
                <div class="bg-white rounded-xl shadow-md p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Faltantes</p>
                            <p class="text-2xl font-bold text-orange-600">
                                {{ stats.faltantes_pendientes || 0 }}
                            </p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="px-4 pb-24">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Acciones Rápidas</h2>
            <div class="grid grid-cols-2 gap-4">
                <button
                    @click="navigateTo('/mobile/ventas/nueva')"
                    class="bg-white rounded-xl shadow-md p-6 text-center hover:bg-blue-50 transition"
                >
                    <div class="bg-blue-100 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-700">Nueva Venta</p>
                </button>

                <button
                    @click="navigateTo('/mobile/productos')"
                    class="bg-white rounded-xl shadow-md p-6 text-center hover:bg-green-50 transition"
                >
                    <div class="bg-green-100 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-700">Productos</p>
                </button>

                <button
                    @click="navigateTo('/mobile/inventario')"
                    class="bg-white rounded-xl shadow-md p-6 text-center hover:bg-purple-50 transition"
                >
                    <div class="bg-purple-100 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-700">Inventario</p>
                </button>

                <button
                    @click="navigateTo('/mobile/gastos')"
                    class="bg-white rounded-xl shadow-md p-6 text-center hover:bg-red-50 transition"
                >
                    <div class="bg-red-100 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-700">Gastos</p>
                </button>
            </div>
        </div>

        <!-- Bottom Navigation -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
            <div class="grid grid-cols-4 gap-1 p-2">
                <button class="flex flex-col items-center py-2 text-blue-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    <span class="text-xs mt-1 font-medium">Inicio</span>
                </button>
                <button @click="navigateTo('/mobile/productos')" class="flex flex-col items-center py-2 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="text-xs mt-1">Productos</span>
                </button>
                <button @click="navigateTo('/mobile/ventas')" class="flex flex-col items-center py-2 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="text-xs mt-1">Ventas</span>
                </button>
                <button @click="navigateTo('/mobile/perfil')" class="flex flex-col items-center py-2 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-xs mt-1">Perfil</span>
                </button>
            </div>
        </div>
    </div>
</template>
