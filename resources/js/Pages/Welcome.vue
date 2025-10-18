<script setup>
import { Head } from '@inertiajs/vue3';
</script>
<template>
    <Head title="Ferretería TOTORO" />
        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-100 to-gray-200">
        <!-- Header -->
        <header class="bg-white shadow-md py-4 px-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <!-- Logo Totoro -->
                <img src="/images/totoro-logo.png" alt="Ferretería TOTORO" class="h-12 w-12 rounded-full shadow-md object-cover hover:scale-105 transition-transform" />
                <span class="text-2xl font-bold text-blue-700">Ferretería TOTORO</span>
            </div>
            <nav class="flex gap-6 text-gray-600 font-semibold">
                <a href="/" class="hover:text-blue-700">Inicio</a>
                <a href="/faltantes" class="hover:text-blue-700">Faltantes</a>
                <a href="/gastos" class="hover:text-blue-700">Gastos</a>
                <a href="/usuarios" class="hover:text-blue-700">Usuarios</a>
                <a href="/ventas" class="hover:text-blue-700">Ventas</a>
                <a href="#" class="hover:text-blue-700">Productos</a>
                <a href="#" class="hover:text-blue-700">Proveedores</a>
                <a href="#" class="hover:text-blue-700">Contacto</a>
            </nav>
        </header>
        <!-- Hero -->
        <section class="py-12 px-8 text-center">
            <h1 class="text-4xl font-extrabold text-gray-800 mb-4">¡Bienvenido a Ferretería TOTORO!</h1>
            <p class="text-lg text-gray-500 mb-8">Todo lo que necesitas para tu hogar y construcción, con atención personalizada y los mejores precios.</p>
            <!-- Imagen de herramientas -->
            <img src="/images/herramientas1.png" alt="Ferretería" class="mx-auto rounded-xl shadow-lg w-full max-w-2xl" />
        </section>
        <!-- Cards informativas -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-8 px-8 py-8">
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                <svg class="w-10 h-10 text-blue-600 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17v2a2 2 0 002 2h2a2 2 0 002-2v-2m6 0v2a2 2 0 002 2h2a2 2 0 002-2v-2M7 13l5-5 5 5" /></svg>
                <span class="text-xl font-bold">1200+</span>
                <span class="text-gray-500">Productos en stock</span>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                <svg class="w-10 h-10 text-green-600 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" /></svg>
                <span class="text-xl font-bold">350</span>
                <span class="text-gray-500">Ventas recientes</span>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                <svg class="w-10 h-10 text-yellow-600 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 01-8 0" /></svg>
                <span class="text-xl font-bold">25</span>
                <span class="text-gray-500">Proveedores activos</span>
            </div>
        </section>
        <!-- Footer -->
        <footer class="bg-white py-6 mt-12 text-center text-gray-400 text-sm">
            © 2025 Ferretería TOTORO · Dirección · Teléfono · Redes sociales
        </footer>
    </div>
</template>
