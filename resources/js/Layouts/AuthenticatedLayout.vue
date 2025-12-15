<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';

const showingNavigationDropdown = ref(false);
const page = usePage();
const { user: authUser, logout, hasRole } = useAuth();

// Get user from OAuth token or fallback to Inertia props
const currentUser = computed(() => {
    return authUser.value || page.props.auth?.user;
});

// Check if user is root
const isRoot = computed(() => {
    return hasRole('root');
});

// Handle logout with OAuth 2.0
const handleLogout = async () => {
    await logout();
    // Redirect will be handled by useAuth composable
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav
                class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-12 w-12"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('ventas.index')"
                                    :active="route().current('ventas.*')"
                                >
                                    Ventas
                                </NavLink>
                                <NavLink
                                    :href="route('proveedores.index')"
                                    :active="route().current('proveedores.*')"
                                >
                                    Proveedores
                                </NavLink>
                                <NavLink
                                    :href="route('productos.index')"
                                    :active="route().current('productos.*')"
                                >
                                    Productos
                                </NavLink>
                                <NavLink
                                    :href="route('inventario.index')"
                                    :active="route().current('inventario.*')"
                                >
                                    Inventario
                                </NavLink>
                                <NavLink
                                    :href="route('faltantes.index')"
                                    :active="route().current('faltantes.*')"
                                >
                                    Faltantes
                                </NavLink>
                                <NavLink
                                    :href="route('usuarios.index')"
                                    :active="route().current('usuarios.*')"
                                >
                                    Usuarios
                                </NavLink>
                                <NavLink
                                    v-if="isRoot"
                                    :href="route('settings.index')"
                                    :active="route().current('settings.*')"
                                >
                                    Configuración
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                {{ currentUser?.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <button
                                            @click="handleLogout"
                                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                                        >
                                            Log Out
                                        </button>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('ventas.index')"
                            :active="route().current('ventas.*')"
                        >
                            Ventas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('proveedores.index')"
                            :active="route().current('proveedores.*')"
                        >
                            Proveedores
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('productos.index')"
                            :active="route().current('productos.*')"
                        >
                            Productos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('inventario.index')"
                            :active="route().current('inventario.*')"
                        >
                            Inventario
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('faltantes.index')"
                            :active="route().current('faltantes.*')"
                        >
                            Faltantes
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('usuarios.index')"
                            :active="route().current('usuarios.*')"
                        >
                            Usuarios
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isRoot"
                            :href="route('settings.index')"
                            :active="route().current('settings.*')"
                        >
                            Configuración
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ currentUser?.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ currentUser?.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <button
                                @click="handleLogout"
                                class="block w-full px-4 py-2 text-start text-base font-medium text-gray-600 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-800 focus:outline-none dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 dark:focus:border-gray-600 dark:focus:bg-gray-700 dark:focus:text-gray-200"
                            >
                                Log Out
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow dark:bg-gray-800"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
