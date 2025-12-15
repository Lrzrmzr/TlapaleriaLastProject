<script setup>
defineProps({
    icon: {
        type: String,
        required: true
    },
    title: {
        type: String,
        default: 'No hay datos'
    },
    description: {
        type: String,
        default: ''
    }
});
</script>

<template>
    <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
        </svg>
        <p class="text-lg font-medium">{{ title }}</p>
        <p v-if="description" class="text-sm mt-1">{{ description }}</p>
    </div>
</template>
