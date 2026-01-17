<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';

const { logout } = useAuth();

const handleLogout = async () => {
    await logout();
};
</script>

<template>
    <Head title="Sin Sucursal Asignada" />

    <div class="min-h-screen bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header con gradiente -->
                <div class="bg-gradient-to-r from-yellow-500 to-orange-600 px-6 py-8 text-center">
                    <svg class="w-20 h-20 mx-auto mb-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h1 class="text-3xl font-bold text-white mb-2">
                        Acceso Restringido
                    </h1>
                    <p class="text-yellow-100 text-sm">
                        No tienes una sucursal asignada
                    </p>
                </div>

                <!-- Contenido -->
                <div class="px-6 py-8">
                    <div class="mb-6">
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">
                                        Sucursal No Asignada
                                    </h3>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        Tu cuenta de usuario está activa, pero necesitas que un administrador te asigne a una sucursal para poder acceder al sistema.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <p class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><strong>¿Qué hacer?</strong> Contacta al administrador de tu sucursal o al administrador general del sistema.</span>
                            </p>
                            <p class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><strong>Una vez asignado:</strong> Podrás iniciar sesión nuevamente y acceder al sistema normalmente.</span>
                            </p>
                            <p class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-purple-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><strong>Importante:</strong> No intentes acceder hasta que te confirmen que tu sucursal ha sido asignada.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Información de contacto -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Información de Contacto
                        </h4>
                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <p>📧 Email: admin@tlapaleria.com</p>
                            <p>📞 Teléfono: 555-0000</p>
                        </div>
                    </div>

                    <!-- Botón de cerrar sesión -->
                    <button
                        @click="handleLogout"
                        class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Cerrar Sesión
                    </button>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                    <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                        Ferretería TOTORO &copy; {{ new Date().getFullYear() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
