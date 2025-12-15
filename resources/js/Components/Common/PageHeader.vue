<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    buttonText: {
        type: String,
        default: 'Nuevo'
    },
    showButton: {
        type: Boolean,
        default: true
    },
    buttonColor: {
        type: String,
        default: 'blue',
        validator: (value) => ['blue', 'green', 'purple', 'orange', 'red'].includes(value)
    }
});

const emit = defineEmits(['action']);

const colorClasses = {
    blue: 'from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700',
    green: 'from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700',
    purple: 'from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700',
    orange: 'from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700',
    red: 'from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700'
};

const getColorClass = (color) => {
    return colorClasses[color] || colorClasses.blue;
};
</script>

<template>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                {{ title }}
            </h2>
            <p v-if="description" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ description }}
            </p>
        </div>
        <div class="flex gap-3">
            <slot name="extra-buttons"></slot>
            <button
                v-if="showButton"
                @click="emit('action')"
                :class="['bg-gradient-to-r text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2', getColorClass(buttonColor)]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ buttonText }}
            </button>
        </div>
    </div>
</template>
