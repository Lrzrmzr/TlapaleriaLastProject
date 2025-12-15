<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

// Props del backend
const props = defineProps({
    mainStats: {
        type: Array,
        required: true
    },
    recentSales: {
        type: Array,
        required: true
    },
    topProducts: {
        type: Array,
        required: true
    },
    alerts: {
        type: Array,
        required: true
    }
});

// Carrusel automático
const currentSlide = ref(0);
const slides = [
    {
        image: '/images/herramientas1.png',
        title: 'Herramientas de Calidad',
        subtitle: 'Las mejores marcas del mercado'
    },
    {
        image: '/images/herramientas2.png',
        title: 'Amplio Inventario',
        subtitle: 'Todo lo que necesitas en un solo lugar'
    },
    {
        image: '/images/herramientas3.png',
        title: 'Precios Competitivos',
        subtitle: 'Calidad al mejor precio'
    }
];

let carouselInterval = null;

onMounted(() => {
    carouselInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % slides.length;
    }, 4000);
});

onUnmounted(() => {
    if (carouselInterval) {
        clearInterval(carouselInterval);
    }
});

const goToSlide = (index) => {
    currentSlide.value = index;
};

// Accesos rápidos
const quickActions = [
    {
        name: 'Nueva Venta',
        description: 'Registrar venta',
        route: 'ventas.index',
        icon: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>`,
        color: 'from-green-500 to-emerald-600',
        hoverColor: 'hover:from-green-600 hover:to-emerald-700'
    },
    {
        name: 'Productos',
        description: 'Gestionar catálogo',
        route: 'productos.index',
        icon: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>`,
        color: 'from-indigo-500 to-blue-600',
        hoverColor: 'hover:from-indigo-600 hover:to-blue-700'
    },
    {
        name: 'Inventario',
        description: 'Control de stock',
        route: 'inventario.index',
        icon: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>`,
        color: 'from-purple-500 to-pink-600',
        hoverColor: 'hover:from-purple-600 hover:to-pink-700'
    },
    {
        name: 'Faltantes',
        description: 'Ver faltantes',
        route: 'faltantes.index',
        icon: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>`,
        color: 'from-orange-500 to-amber-600',
        hoverColor: 'hover:from-orange-600 hover:to-amber-700'
    }
];
</script>

<template>
    <Head title="Dashboard - Ferretería TOTORO" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                        Bienvenido, {{ $page.props.auth.user.name }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Aquí está el resumen de tu ferretería hoy
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Fecha</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        {{ new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <div
                        v-for="(stat, index) in mainStats"
                        :key="index"
                        :class="stat.lightBg"
                        class="relative overflow-hidden rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                                    {{ stat.title }}
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stat.value }}
                                </p>
                                <div class="mt-2 flex items-center text-sm">
                                    <span
                                        :class="stat.positive ? 'text-green-600' : 'text-red-600'"
                                        class="font-semibold"
                                    >
                                        {{ stat.change }}
                                    </span>
                                    <span class="ml-2 text-gray-600 dark:text-gray-400">vs ayer</span>
                                </div>
                            </div>
                            <div
                                :class="`bg-gradient-to-br ${stat.bgColor}`"
                                class="flex h-16 w-16 items-center justify-center rounded-xl text-white shadow-lg"
                                v-html="stat.icon"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Carrusel Automático -->
                <div class="mb-8 relative overflow-hidden rounded-3xl shadow-2xl bg-gradient-to-br from-gray-900 to-gray-800">
                    <div class="relative h-96">
                        <transition-group name="fade">
                            <div
                                v-for="(slide, index) in slides"
                                :key="index"
                                v-show="currentSlide === index"
                                class="absolute inset-0"
                            >
                                <img
                                    :src="slide.image"
                                    :alt="slide.title"
                                    class="w-full h-full object-cover"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-8">
                                    <h3 class="text-4xl font-bold text-white mb-2">{{ slide.title }}</h3>
                                    <p class="text-xl text-gray-200">{{ slide.subtitle }}</p>
                                </div>
                            </div>
                        </transition-group>
                    </div>

                    <!-- Dots Navigation -->
                    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                        <button
                            v-for="(_, index) in slides"
                            :key="index"
                            @click="goToSlide(index)"
                            :class="currentSlide === index ? 'bg-white w-8' : 'bg-white/50 w-3'"
                            class="h-3 rounded-full transition-all duration-300 hover:bg-white"
                        ></button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Accesos Rápidos -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                <svg class="w-7 h-7 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Accesos Rápidos
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <Link
                                    v-for="(action, index) in quickActions"
                                    :key="index"
                                    :href="route(action.route)"
                                    :class="`bg-gradient-to-br ${action.color} ${action.hoverColor}`"
                                    class="group relative overflow-hidden rounded-2xl p-6 text-white shadow-lg transition-all duration-300 hover:shadow-2xl hover:scale-105 cursor-pointer"
                                >
                                    <div class="relative z-10">
                                        <div class="mb-3 transform transition-transform duration-300 group-hover:scale-110" v-html="action.icon"></div>
                                        <h4 class="text-lg font-bold mb-1">{{ action.name }}</h4>
                                        <p class="text-sm text-white/80">{{ action.description }}</p>
                                    </div>
                                    <div class="absolute -right-6 -bottom-6 h-24 w-24 rounded-full bg-white/10 transform transition-transform duration-300 group-hover:scale-150"></div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Alertas -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                            <svg class="w-7 h-7 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Alertas
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="(alert, index) in alerts"
                                :key="index"
                                :class="alert.color"
                                class="flex items-start space-x-3 p-4 rounded-xl border-l-4 transition-all duration-300 hover:shadow-md"
                            >
                                <span class="text-2xl">{{ alert.icon }}</span>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ alert.title }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ alert.message }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ventas Recientes y Top Productos -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Ventas Recientes -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                            <svg class="w-7 h-7 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Ventas Recientes
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="(sale, index) in recentSales"
                                :key="index"
                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ sale.product }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                        {{ sale.customer }} • {{ sale.quantity }} unidades
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-green-600 dark:text-green-400">{{ sale.amount }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ sale.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Productos -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                            <svg class="w-7 h-7 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Top Productos
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="(product, index) in topProducts"
                                :key="index"
                                class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 text-white font-bold text-lg shadow-lg mr-4">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ product.name }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ product.sales }} ventas</p>
                                </div>
                                <div class="text-right">
                                    <p :class="product.color" class="font-bold">{{ product.revenue }}</p>
                                    <div class="flex items-center justify-end mt-1">
                                        <svg
                                            v-if="product.trend === 'up'"
                                            class="w-4 h-4 text-green-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                        </svg>
                                        <svg
                                            v-else
                                            class="w-4 h-4 text-red-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Animación para el carrusel */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 1s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
