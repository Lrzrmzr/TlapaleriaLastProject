<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    icon: {
        type: String,
        required: true
    },
    colorFrom: {
        type: String,
        default: 'blue'
    },
    colorTo: {
        type: String,
        default: 'cyan'
    }
});

const colorClasses = {
    blue: {
        bg: 'from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20',
        text: 'text-blue-600 dark:text-blue-400',
        gradient: 'from-blue-500 to-cyan-600'
    },
    green: {
        bg: 'from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20',
        text: 'text-green-600 dark:text-green-400',
        gradient: 'from-green-500 to-emerald-600'
    },
    orange: {
        bg: 'from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20',
        text: 'text-orange-600 dark:text-orange-400',
        gradient: 'from-orange-500 to-amber-600'
    },
    purple: {
        bg: 'from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20',
        text: 'text-purple-600 dark:text-purple-400',
        gradient: 'from-purple-500 to-pink-600'
    },
    red: {
        bg: 'from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20',
        text: 'text-red-600 dark:text-red-400',
        gradient: 'from-red-500 to-rose-600'
    }
};

const getColorClass = (color, type) => {
    return colorClasses[color]?.[type] || colorClasses.blue[type];
};
</script>

<template>
    <div :class="['bg-gradient-to-br rounded-2xl p-6 shadow-lg', getColorClass(colorFrom, 'bg')]">
        <div class="flex items-center justify-between">
            <div>
                <p :class="['text-sm font-medium uppercase', getColorClass(colorFrom, 'text')]">
                    {{ title }}
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                    {{ value }}
                </p>
            </div>
            <div :class="['bg-gradient-to-br w-16 h-16 rounded-xl flex items-center justify-center shadow-lg', getColorClass(colorFrom, 'gradient')]">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
                </svg>
            </div>
        </div>
    </div>
</template>
