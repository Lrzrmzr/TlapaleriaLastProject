<script setup>
defineProps({
    product: {
        type: Object,
        required: true
    }
});

const getCategoryIcon = (category) => {
    const icons = {
        'Herramientas': `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>`,
        'Construcción': `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>`,
        'Electricidad': `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>`,
        'Plomería': `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>`,
        'Pintura': `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
        </svg>`,
        'default': `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>`
    };
    return icons[category] || icons['default'];
};

const getCategoryColor = (category) => {
    const colors = {
        'Herramientas': 'from-blue-500 to-blue-600',
        'Construcción': 'from-amber-500 to-orange-600',
        'Electricidad': 'from-yellow-500 to-yellow-600',
        'Plomería': 'from-cyan-500 to-blue-600',
        'Pintura': 'from-purple-500 to-pink-600',
        'default': 'from-gray-500 to-gray-600'
    };
    return colors[category] || colors['default'];
};
</script>

<template>
    <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
        <!-- Category Badge -->
        <div class="absolute top-4 right-4 z-10">
            <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-xs font-semibold text-gray-700 rounded-full shadow-md">
                {{ product.category || 'General' }}
            </span>
        </div>

        <!-- Icon Container -->
        <div :class="`bg-gradient-to-br ${getCategoryColor(product.category)}`" class="h-48 flex items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-black/5 group-hover:bg-black/10 transition-colors"></div>
            <div class="relative text-white transform group-hover:scale-110 transition-transform duration-300" v-html="getCategoryIcon(product.category)"></div>

            <!-- Decorative Elements -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-orange-600 transition-colors">
                {{ product.name }}
            </h3>
            <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                {{ product.description || 'Producto de calidad disponible en nuestra ferretería' }}
            </p>

            <!-- Info Button -->
            <button class="w-full mt-4 px-4 py-2 bg-gradient-to-r from-blue-700 to-purple-700 text-white rounded-lg font-medium hover:from-blue-800 hover:to-purple-800 transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg flex items-center justify-center space-x-2">
                <span>Consultar disponibilidad</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>
        </div>

        <!-- Hover Effect Border -->
        <div class="absolute inset-0 border-2 border-transparent group-hover:border-blue-700 rounded-2xl transition-colors pointer-events-none"></div>
    </div>
</template>
