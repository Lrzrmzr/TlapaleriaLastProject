<script setup>
const features = [
    {
        title: 'Atención Personalizada',
        description: 'Nuestro equipo experto te asesora en cada compra para que encuentres exactamente lo que necesitas',
        icon: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>`,
        color: 'from-blue-500 to-indigo-600',
        delay: '0'
    },
    {
        title: 'Amplio Inventario',
        description: 'Miles de productos disponibles para todas tus necesidades de construcción y hogar',
        icon: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>`,
        color: 'from-orange-500 to-red-600',
        delay: '100'
    },
    {
        title: 'Mejores Precios',
        description: 'Precios competitivos y promociones constantes para que aproveches al máximo tu presupuesto',
        icon: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>`,
        color: 'from-green-500 to-emerald-600',
        delay: '200'
    },
    {
        title: 'Entrega Rápida',
        description: 'Servicio de entrega a domicilio para que recibas tus productos cuando los necesites',
        icon: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>`,
        color: 'from-purple-500 to-pink-600',
        delay: '300'
    }
];
</script>

<template>
    <section id="servicios" class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    ¿Por qué elegirnos?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Nos comprometemos a brindarte la mejor experiencia en cada visita
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div
                    v-for="(feature, index) in features"
                    :key="index"
                    :style="`animation-delay: ${feature.delay}ms`"
                    class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in-up"
                >
                    <!-- Icon -->
                    <div :class="`bg-gradient-to-br ${feature.color}`" class="w-20 h-20 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <div v-html="feature.icon"></div>
                    </div>

                    <!-- Content -->
                    <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-700 transition-colors">
                        {{ feature.title }}
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        {{ feature.description }}
                    </p>

                    <!-- Decorative Element -->
                    <div class="mt-6 h-1 w-0 group-hover:w-full bg-gradient-to-r from-blue-700 to-purple-700 rounded-full transition-all duration-500"></div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-blue-700 mb-2">20+</div>
                    <div class="text-gray-600 font-medium">Años de Experiencia</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-blue-700 mb-2">5000+</div>
                    <div class="text-gray-600 font-medium">Productos</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-blue-700 mb-2">10000+</div>
                    <div class="text-gray-600 font-medium">Clientes Satisfechos</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-blue-700 mb-2">100%</div>
                    <div class="text-gray-600 font-medium">Garantía</div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0;
}
</style>
