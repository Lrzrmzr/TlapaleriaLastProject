<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        required: true
    },
    placeholder: {
        type: String,
        default: 'Buscar...'
    },
    label: {
        type: String,
        default: 'Buscar:'
    },
    total: {
        type: Number,
        default: 0
    },
    filtered: {
        type: Number,
        default: 0
    },
    showCount: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue']);

const inputValue = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-1 w-full">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                    {{ label }}
                </label>
                <input
                    v-model="inputValue"
                    type="text"
                    :placeholder="placeholder"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                />
            </div>
            <slot name="filters"></slot>
            <div v-if="showCount" class="text-sm text-gray-600 dark:text-gray-400 mt-6">
                Mostrando {{ filtered }} de {{ total }}
            </div>
        </div>
    </div>
</template>
