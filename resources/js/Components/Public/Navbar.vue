<script setup>
import { ref } from 'vue';

const mobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
        mobileMenuOpen.value = false;
    }
};
</script>

<template>
    <nav class="fixed w-full top-0 z-50 bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-3 cursor-pointer" @click="scrollToSection('hero')">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-700 to-purple-700 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 transition-colors">
                            Ferretería TOTORO
                        </h1>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <button
                        @click="scrollToSection('hero')"
                        class="font-medium text-gray-700 transition-colors hover:text-blue-700"
                    >
                        Inicio
                    </button>
                    <button
                        @click="scrollToSection('productos')"
                        class="font-medium text-gray-700 transition-colors hover:text-blue-700"
                    >
                        Productos
                    </button>
                    <button
                        @click="scrollToSection('servicios')"
                        class="font-medium text-gray-700 transition-colors hover:text-blue-700"
                    >
                        Servicios
                    </button>
                    <button
                        @click="scrollToSection('contacto')"
                        class="font-medium text-gray-700 transition-colors hover:text-blue-700"
                    >
                        Contacto
                    </button>
                </div>

                <!-- Mobile Menu Button -->
                <button
                    @click="toggleMobileMenu"
                    class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <svg
                        class="w-6 h-6 text-gray-900"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            v-if="!mobileMenuOpen"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <div
                v-show="mobileMenuOpen"
                class="md:hidden bg-white border-t shadow-xl"
            >
                <div class="px-4 py-6 space-y-3">
                    <button
                        @click="scrollToSection('hero')"
                        class="block w-full text-left px-4 py-3 text-gray-700 font-medium hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors"
                    >
                        Inicio
                    </button>
                    <button
                        @click="scrollToSection('productos')"
                        class="block w-full text-left px-4 py-3 text-gray-700 font-medium hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors"
                    >
                        Productos
                    </button>
                    <button
                        @click="scrollToSection('servicios')"
                        class="block w-full text-left px-4 py-3 text-gray-700 font-medium hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors"
                    >
                        Servicios
                    </button>
                    <button
                        @click="scrollToSection('contacto')"
                        class="block w-full text-left px-4 py-3 text-gray-700 font-medium hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors"
                    >
                        Contacto
                    </button>
                </div>
            </div>
        </transition>
    </nav>
</template>
