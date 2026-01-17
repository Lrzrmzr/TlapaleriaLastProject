<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';

const props = defineProps({
    isCollapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle']);

const page = usePage();
const { hasRole } = useAuth();

// Check if user is root
const isRoot = computed(() => {
    return hasRole('root');
});

// Get faltantes count from page props if available
const faltantesPendientes = computed(() => {
    return page.props.faltantesPendientes || 0;
});

// Menu items organized by sections
const menuSections = computed(() => [
    {
        title: 'Principal',
        items: [
            {
                name: 'Dashboard',
                route: 'dashboard',
                icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                active: route().current('dashboard'),
            },
        ],
    },
    {
        title: 'Sucursales',
        items: [
            {
                name: 'Sucursales',
                route: 'sucursales.index',
                icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                active: route().current('sucursales.*'),
            },
            {
                name: 'Inventario',
                route: 'inventario.index',
                icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                active: route().current('inventario.*'),
            },
        ],
    },
    {
        title: 'Productos',
        items: [
            {
                name: 'Productos',
                route: 'productos.index',
                icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                active: route().current('productos.*'),
            },
            {
                name: 'Categorías',
                route: 'categorias.index',
                icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                active: route().current('categorias.*'),
            },
            {
                name: 'Proveedores',
                route: 'proveedores.index',
                icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                active: route().current('proveedores.*'),
            },
            {
                name: 'Faltantes',
                route: 'faltantes.index',
                icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                active: route().current('faltantes.*'),
                badge: faltantesPendientes.value > 0 ? faltantesPendientes.value : null,
            },
        ],
    },
    {
        title: 'Ventas & Gastos',
        items: [
            {
                name: 'Ventas',
                route: 'ventas.index',
                icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                active: route().current('ventas.*'),
            },
            {
                name: 'Gastos',
                route: 'gastos.index',
                icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                active: route().current('gastos.*'),
            },
        ],
    },
    {
        title: 'Administración',
        items: [
            {
                name: 'Usuarios',
                route: 'usuarios.index',
                icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                active: route().current('usuarios.*'),
            },
            {
                name: 'Auditoría',
                route: 'auditoria.index',
                icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                active: route().current('auditoria.*'),
            },
            {
                name: 'Configuración',
                route: 'settings.index',
                icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                active: route().current('settings.*'),
                visible: isRoot.value,
            },
        ].filter(item => item.visible !== false),
    },
]);

const toggleSidebar = () => {
    emit('toggle');
};
</script>

<template>
    <div
        :class="[
            'fixed left-0 top-0 h-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out z-40',
            isCollapsed ? 'w-16' : 'w-64'
        ]"
    >
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
            <div v-if="!isCollapsed" class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                </div>
                <div class="text-xl font-bold text-gray-800 dark:text-white">
                    TOTORO
                </div>
            </div>

            <button
                @click="toggleSidebar"
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                :class="isCollapsed ? 'mx-auto' : ''"
            >
                <svg
                    class="w-5 h-5 text-gray-600 dark:text-gray-300 transition-transform"
                    :class="{ 'rotate-180': isCollapsed }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4 space-y-6">
            <div
                v-for="section in menuSections"
                :key="section.title"
                class="px-3"
            >
                <!-- Section Title -->
                <h3
                    v-if="!isCollapsed"
                    class="px-3 mb-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >
                    {{ section.title }}
                </h3>

                <!-- Divider when collapsed -->
                <div v-else class="border-t border-gray-200 dark:border-gray-700 mb-2"></div>

                <!-- Menu Items -->
                <ul class="space-y-1">
                    <li v-for="item in section.items" :key="item.route">
                        <Link
                            :href="route(item.route)"
                            :class="[
                                'flex items-center px-3 py-2 rounded-lg transition-all duration-200 group relative',
                                item.active
                                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            <!-- Icon -->
                            <svg
                                class="flex-shrink-0 w-5 h-5"
                                :class="[
                                    item.active
                                        ? 'text-blue-600 dark:text-blue-400'
                                        : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300',
                                    isCollapsed ? '' : 'mr-3'
                                ]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>

                            <!-- Label -->
                            <span
                                v-if="!isCollapsed"
                                class="text-sm font-medium"
                            >
                                {{ item.name }}
                            </span>

                            <!-- Badge -->
                            <span
                                v-if="item.badge && !isCollapsed"
                                class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400"
                            >
                                {{ item.badge }}
                            </span>

                            <!-- Badge when collapsed (dot indicator) -->
                            <span
                                v-if="item.badge && isCollapsed"
                                class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"
                            ></span>

                            <!-- Tooltip when collapsed -->
                            <div
                                v-if="isCollapsed"
                                class="absolute left-full ml-2 px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50"
                            >
                                {{ item.name }}
                                <span v-if="item.badge" class="ml-2 px-1.5 py-0.5 text-xs rounded-full bg-red-500 text-white">
                                    {{ item.badge }}
                                </span>
                            </div>
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</template>
