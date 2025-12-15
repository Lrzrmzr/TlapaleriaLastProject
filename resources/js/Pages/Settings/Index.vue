<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Array,
    stats: Object,
});

// Convertir settings array a objeto para fácil acceso
const settingsObj = ref({});
props.settings.forEach(setting => {
    settingsObj.value[setting.key] = setting.value;
});

// Form para actualizar configuraciones
const formSettings = useForm({
    enable_ventas_libres: settingsObj.value.enable_ventas_libres,
    enable_faltantes_manual: settingsObj.value.enable_faltantes_manual,
    stock_bajo_threshold: settingsObj.value.stock_bajo_threshold,
});

function updateSetting(key, value) {
    router.post(route('settings.update'), {
        key: key,
        value: value
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function generarFaltantes() {
    if (confirm('¿Generar faltantes automáticamente para productos con stock bajo?')) {
        router.post(route('settings.generar-faltantes'), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Configuración del Sistema - Ferretería TOTORO" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Configuración del Sistema
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Panel de administración exclusivo para Root
                </p>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Alerta de advertencia -->
                <div class="mb-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-l-4 border-amber-500 p-6 rounded-lg shadow-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-amber-800 dark:text-amber-300">
                                Configuración Sensible
                            </h3>
                            <p class="text-sm text-amber-700 dark:text-amber-400 mt-2">
                                Esta sección es exclusiva para el administrador Root. Los cambios aquí afectan el comportamiento global del sistema.
                                <strong>Ventas Libres</strong> y <strong>Faltantes Manuales</strong> son funciones temporales diseñadas para la etapa de adaptación inicial.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-orange-600 dark:text-orange-400 uppercase">Stock Bajo</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.productos_stock_bajo }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-orange-500 to-amber-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-red-600 dark:text-red-400 uppercase">Agotados</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.productos_agotados }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-red-500 to-rose-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-600 dark:text-purple-400 uppercase">Faltantes Pendientes</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.faltantes_pendientes }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-purple-500 to-pink-600 w-16 h-16 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuraciones del Sistema -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden mb-6">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                        <h3 class="text-2xl font-bold text-white flex items-center">
                            <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Funciones Temporales (Adaptación)
                        </h3>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Ventas Libres -->
                        <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Ventas Libres
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Permite registrar ventas de productos no incluidos en el inventario. Útil mientras se carga el catálogo inicial.
                                </p>
                            </div>
                            <div class="ml-6">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :checked="formSettings.enable_ventas_libres"
                                        @change="updateSetting('enable_ventas_libres', $event.target.checked ? 1 : 0)"
                                        class="sr-only peer"
                                    />
                                    <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        {{ formSettings.enable_ventas_libres ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Faltantes Manuales -->
                        <div class="flex items-center justify-between p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl border border-purple-200 dark:border-purple-800">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    Faltantes Manuales
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Permite el registro manual de productos faltantes. Una vez adaptado el sistema, los faltantes se generarán automáticamente.
                                </p>
                            </div>
                            <div class="ml-6">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :checked="formSettings.enable_faltantes_manual"
                                        @change="updateSetting('enable_faltantes_manual', $event.target.checked ? 1 : 0)"
                                        class="sr-only peer"
                                    />
                                    <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        {{ formSettings.enable_faltantes_manual ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Stock Bajo Threshold -->
                        <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-xl border border-orange-200 dark:border-orange-800">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center mb-3">
                                <svg class="w-6 h-6 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Umbral de Stock Bajo
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Cantidad mínima de stock para considerar un producto como "stock bajo" y generar alertas.
                            </p>
                            <div class="flex items-center gap-4">
                                <input
                                    v-model.number="formSettings.stock_bajo_threshold"
                                    type="number"
                                    min="1"
                                    max="100"
                                    class="w-32 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                                />
                                <button
                                    @click="updateSetting('stock_bajo_threshold', formSettings.stock_bajo_threshold)"
                                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-semibold transition-colors"
                                >
                                    Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Automáticas -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                        <h3 class="text-2xl font-bold text-white flex items-center">
                            <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Acciones Automáticas
                        </h3>
                    </div>

                    <div class="p-6">
                        <div class="flex items-start justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    Generar Faltantes Automáticamente
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    Crea registros de faltantes para todos los productos que tengan stock igual o menor al umbral configurado.
                                    Solo se generarán faltantes para productos que aún no tengan uno pendiente.
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    Productos detectados con stock bajo: <strong>{{ stats.productos_stock_bajo }}</strong>
                                </p>
                            </div>
                            <div class="ml-6">
                                <button
                                    @click="generarFaltantes"
                                    class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-semibold shadow-lg transition-all flex items-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Generar Ahora
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
