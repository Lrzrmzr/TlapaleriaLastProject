<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const currentSlide = ref(0);
const slides = [
    {
        title: 'Todo para tu Hogar y Construcción',
        subtitle: 'Encuentra las mejores herramientas y materiales con atención personalizada',
        gradient: 'from-blue-800 to-purple-800'
    },
    {
        title: 'Calidad y Precios Competitivos',
        subtitle: 'Productos de las mejores marcas al alcance de tu presupuesto',
        gradient: 'from-indigo-800 to-blue-900'
    },
    {
        title: 'Experiencia y Confianza',
        subtitle: 'Más de 20 años sirviendo a nuestra comunidad',
        gradient: 'from-purple-800 to-pink-800'
    }
];

let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % slides.length;
    }, 5000);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});

const goToSlide = (index) => {
    currentSlide.value = index;
};

const scrollToProducts = () => {
    const element = document.getElementById('productos');
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <section id="hero" class="relative h-screen overflow-hidden">
        <!-- Background Slides -->
        <div class="absolute inset-0">
            <transition-group name="fade">
                <div
                    v-for="(slide, index) in slides"
                    :key="index"
                    v-show="currentSlide === index"
                    :class="`bg-gradient-to-br ${slide.gradient}`"
                    class="absolute inset-0 w-full h-full"
                >
                    <div class="absolute inset-0 bg-black/20"></div>
                </div>
            </transition-group>
        </div>

        <!-- Animated Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-white rounded-full mix-blend-overlay filter blur-3xl animate-blob"></div>
            <div class="absolute top-1/3 right-1/4 w-72 h-72 bg-white rounded-full mix-blend-overlay filter blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-1/4 left-1/3 w-72 h-72 bg-white rounded-full mix-blend-overlay filter blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <!-- Content -->
        <div class="relative h-full flex items-center justify-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <transition-group
                    name="slide-fade"
                    tag="div"
                >
                    <div
                        v-for="(slide, index) in slides"
                        :key="index"
                        v-show="currentSlide === index"
                        class="space-y-8"
                    >
                        <h1 class="text-5xl md:text-7xl font-bold text-white drop-shadow-2xl leading-tight">
                            {{ slide.title }}
                        </h1>
                        <p class="text-xl md:text-2xl text-white/90 max-w-3xl mx-auto drop-shadow-lg">
                            {{ slide.subtitle }}
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mt-12">
                            <button
                                @click="scrollToProducts"
                                class="group px-8 py-4 bg-white text-blue-800 rounded-full font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:scale-105 transition-all duration-300 flex items-center space-x-2"
                            >
                                <span>Ver Productos</span>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </button>
                            <a
                                href="tel:+123456789"
                                class="px-8 py-4 bg-transparent border-2 border-white text-white rounded-full font-bold text-lg hover:bg-white hover:text-blue-800 transform hover:scale-105 transition-all duration-300"
                            >
                                Contáctanos
                            </a>
                        </div>
                    </div>
                </transition-group>

                <!-- Slide Indicators -->
                <div class="absolute bottom-12 left-1/2 transform -translate-x-1/2 flex space-x-3">
                    <button
                        v-for="(_, index) in slides"
                        :key="index"
                        @click="goToSlide(index)"
                        :class="[
                            'transition-all duration-300 rounded-full',
                            currentSlide === index
                                ? 'w-12 h-3 bg-white'
                                : 'w-3 h-3 bg-white/50 hover:bg-white/75'
                        ]"
                    ></button>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </div>
    </section>
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

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 1s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-fade-enter-active {
    transition: all 0.8s ease;
}

.slide-fade-leave-active {
    transition: all 0.5s ease;
}

.slide-fade-enter-from {
    transform: translateY(30px);
    opacity: 0;
}

.slide-fade-leave-to {
    transform: translateY(-30px);
    opacity: 0;
}
</style>
