<script setup>
import { ref, computed } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';

const showingMobileMenu = ref(false);
const sidebarCollapsed = ref(false);
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

// Toggle sidebar collapse
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

// Toggle mobile menu
const toggleMobileMenu = () => {
    showingMobileMenu.value = !showingMobileMenu.value;
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Desktop Sidebar -->
        <div class="hidden lg:block">
            <Sidebar :is-collapsed="sidebarCollapsed" @toggle="toggleSidebar" />
        </div>

        <!-- Mobile Sidebar Overlay -->
        <div
            v-if="showingMobileMenu"
            class="fixed inset-0 z-40 lg:hidden"
            @click="showingMobileMenu = false"
        >
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
        </div>

        <!-- Mobile Sidebar -->
        <div
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 transform transition-transform duration-300 ease-in-out lg:hidden',
                showingMobileMenu ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                <div class="text-xl font-bold text-gray-800 dark:text-white">TOTORO</div>
                <button
                    @click="showingMobileMenu = false"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <Sidebar :is-collapsed="false" />
        </div>

        <!-- Main Content -->
        <div
            :class="[
                'min-h-screen transition-all duration-300',
                sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64'
            ]"
        >
            <!-- Top Bar -->
            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-30">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Mobile Menu Button -->
                        <button
                            @click="toggleMobileMenu"
                            class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 lg:hidden"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Page Title (from slot if available) -->
                        <div v-if="$slots.header" class="flex-1">
                            <slot name="header" />
                        </div>
                        <div v-else class="flex-1"></div>

                        <!-- Right Side: User Dropdown -->
                        <div class="flex items-center space-x-4">
                            <!-- User Dropdown -->
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white font-semibold text-sm">
                                            {{ currentUser?.name?.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="hidden md:block text-left">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ currentUser?.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ currentUser?.email }}
                                            </div>
                                        </div>
                                        <svg
                                            class="w-4 h-4 text-gray-500 dark:text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Perfil
                                    </DropdownLink>
                                    <button
                                        @click="handleLogout"
                                        class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                                    >
                                        Cerrar Sesión
                                    </button>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
