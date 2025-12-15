<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Navbar from '@/Components/Public/Navbar.vue';
import Hero from '@/Components/Public/Hero.vue';
import Features from '@/Components/Public/Features.vue';
import ProductCard from '@/Components/Public/ProductCard.vue';
import Footer from '@/Components/Public/Footer.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    }
});

const selectedCategory = ref('Todos');
const searchQuery = ref('');

const categories = computed(() => {
    const cats = ['Todos', ...new Set(props.products.map(p => p.category).filter(Boolean))];
    return cats;
});

const filteredProducts = computed(() => {
    let filtered = props.products;

    if (selectedCategory.value !== 'Todos') {
        filtered = filtered.filter(p => p.category === selectedCategory.value);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p =>
            p.name.toLowerCase().includes(query) ||
            (p.description && p.description.toLowerCase().includes(query))
        );
    }

    return filtered;
});

const selectCategory = (category) => {
    selectedCategory.value = category;
};
</script>

<template>
    <Head>
        <title>Ferretería TOTORO - Todo para tu hogar y construcción</title>
        <meta name="description" content="Encuentra las mejores herramientas y materiales para construcción. Calidad, variedad y atención personalizada.">
    </Head>

    <div class="min-h-screen bg-white">
        <!-- Navbar -->
        <Navbar />

        <!-- Hero Section -->
        <Hero />

        <!-- Features Section -->
        <Features />

        <!-- Products Section -->
        <section id="productos" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        Nuestros Productos
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Explora nuestro amplio catálogo de productos de calidad
                    </p>
                </div>

                <!-- Search Bar -->
                <div class="mb-8 max-w-2xl mx-auto">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar productos..."
                            class="w-full px-6 py-4 pl-14 rounded-full border-2 border-gray-200 focus:border-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-700/10 transition-all"
                        >
                        <svg class="absolute left-5 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="mb-12 flex flex-wrap justify-center gap-3">
                    <button
                        v-for="category in categories"
                        :key="category"
                        @click="selectCategory(category)"
                        :class="[
                            'px-6 py-3 rounded-full font-medium transition-all duration-300 transform hover:scale-105',
                            selectedCategory === category
                                ? 'bg-gradient-to-r from-blue-700 to-purple-700 text-white shadow-lg'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]"
                    >
                        {{ category }}
                    </button>
                </div>

                <!-- Products Grid -->
                <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <ProductCard
                        v-for="product in filteredProducts"
                        :key="product.id"
                        :product="product"
                    />
                </div>

                <!-- No Products Message -->
                <div v-else class="text-center py-20">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                        No se encontraron productos
                    </h3>
                    <p class="text-gray-600 mb-6">
                        Intenta con otra búsqueda o categoría
                    </p>
                    <button
                        @click="searchQuery = ''; selectedCategory = 'Todos'"
                        class="px-6 py-3 bg-gradient-to-r from-blue-700 to-purple-700 text-white rounded-full font-medium hover:from-blue-800 hover:to-purple-800 transition-all transform hover:scale-105"
                    >
                        Ver todos los productos
                    </button>
                </div>

                <!-- Call to Action -->
                <div class="mt-20 bg-gradient-to-br from-blue-800 to-purple-800 rounded-3xl p-12 text-center shadow-2xl">
                    <h3 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        ¿No encuentras lo que buscas?
                    </h3>
                    <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                        Contáctanos y con gusto te ayudaremos a encontrar el producto perfecto para tu proyecto
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a
                            href="tel:+123456789"
                            class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-800 rounded-full font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 space-x-2"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>Llamar ahora</span>
                        </a>
                        <a
                            href="#contacto"
                            class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white rounded-full font-bold text-lg hover:bg-white hover:text-blue-800 transform hover:scale-105 transition-all duration-300 space-x-2"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Visítanos</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<style scoped>
@keyframes blob {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}
</style>
