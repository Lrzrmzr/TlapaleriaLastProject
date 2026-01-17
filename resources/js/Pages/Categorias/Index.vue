<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
});

const showModal = ref(false);
const editingCategory = ref(null);
const searchTerm = ref('');

const form = useForm({
    name: '',
    description: '',
    icon: '',
    color: '#3B82F6',
    active: true,
    order: 0,
});

// Filtrar categorías por búsqueda
const filteredCategories = computed(() => {
    if (!searchTerm.value) return props.categories;

    return props.categories.filter(cat =>
        cat.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        (cat.description && cat.description.toLowerCase().includes(searchTerm.value.toLowerCase()))
    );
});

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    form.color = '#3B82F6';
    form.active = true;
    form.order = 0;
    showModal.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.description = category.description || '';
    form.icon = category.icon || '';
    form.color = category.color;
    form.active = category.active;
    form.order = category.order;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
    form.reset();
};

const submit = () => {
    if (editingCategory.value) {
        // Actualizar
        form.put(route('categorias.update', editingCategory.value.id), {
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        // Crear
        form.post(route('categorias.store'), {
            onSuccess: () => {
                closeModal();
            },
        });
    }
};

const deleteCategory = (category) => {
    if (confirm(`¿Estás seguro de eliminar la categoría "${category.name}"?`)) {
        form.delete(route('categorias.destroy', category.id));
    }
};

// Colores predefinidos
const predefinedColors = [
    { name: 'Azul', value: '#3B82F6' },
    { name: 'Rojo', value: '#EF4444' },
    { name: 'Verde', value: '#10B981' },
    { name: 'Amarillo', value: '#F59E0B' },
    { name: 'Púrpura', value: '#8B5CF6' },
    { name: 'Rosa', value: '#EC4899' },
    { name: 'Marrón', value: '#8B4513' },
    { name: 'Gris', value: '#6B7280' },
    { name: 'Índigo', value: '#6366F1' },
    { name: 'Cian', value: '#06B6D4' },
];

// Emojis sugeridos
const suggestedEmojis = ['🔨', '🧱', '🚰', '⚡', '🎨', '🌱', '🔧', '🔩', '🔒', '🧹', '🛠️', '⚙️', '🏗️', '🪛', '📐'];
</script>

<template>
    <Head title="Categorías" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Categorías
                </h2>
                <button
                    @click="openCreateModal"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    + Nueva Categoría
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Buscador -->
                <div class="mb-6">
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Buscar categorías..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <!-- Grid de Categorías -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="category in filteredCategories"
                        :key="category.id"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6 border-l-4 transition-all hover:shadow-lg"
                        :style="{ borderLeftColor: category.color }"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl">{{ category.icon || '📁' }}</span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ category.name }}
                                    </h3>
                                    <span
                                        class="text-xs px-2 py-1 rounded-full"
                                        :class="category.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                    >
                                        {{ category.active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                            {{ category.description || 'Sin descripción' }}
                        </p>

                        <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <span>📦</span>
                                <span>{{ category.products_count }} productos</span>
                            </div>

                            <div class="flex gap-2">
                                <button
                                    @click="openEditModal(category)"
                                    class="text-blue-600 hover:text-blue-800 p-2"
                                    title="Editar"
                                >
                                    ✏️
                                </button>
                                <button
                                    @click="deleteCategory(category)"
                                    class="text-red-600 hover:text-red-800 p-2"
                                    title="Eliminar"
                                    :disabled="category.products_count > 0"
                                    :class="{ 'opacity-50 cursor-not-allowed': category.products_count > 0 }"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay categorías -->
                <div v-if="filteredCategories.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ searchTerm ? 'No se encontraron categorías con ese criterio' : 'No hay categorías registradas' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">
                        {{ editingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}
                    </h3>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre *
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Ej: Carpintería"
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Descripción
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Describe esta categoría..."
                            ></textarea>
                        </div>

                        <!-- Icono -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Icono (Emoji)
                            </label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <button
                                    v-for="emoji in suggestedEmojis"
                                    :key="emoji"
                                    type="button"
                                    @click="form.icon = emoji"
                                    class="text-2xl hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded transition-colors"
                                    :class="{ 'bg-blue-100 dark:bg-blue-900': form.icon === emoji }"
                                >
                                    {{ emoji }}
                                </button>
                            </div>
                            <input
                                v-model="form.icon"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="O escribe tu emoji"
                            />
                        </div>

                        <!-- Color -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Color
                            </label>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <button
                                    v-for="colorOption in predefinedColors"
                                    :key="colorOption.value"
                                    type="button"
                                    @click="form.color = colorOption.value"
                                    class="w-10 h-10 rounded-full border-2 hover:scale-110 transition-transform"
                                    :style="{ backgroundColor: colorOption.value }"
                                    :class="{ 'border-gray-900 scale-110': form.color === colorOption.value }"
                                    :title="colorOption.name"
                                ></button>
                            </div>
                            <div class="flex gap-2 items-center">
                                <input
                                    v-model="form.color"
                                    type="color"
                                    class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                                />
                                <input
                                    v-model="form.color"
                                    type="text"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="#3B82F6"
                                />
                            </div>
                        </div>

                        <!-- Orden -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Orden
                            </label>
                            <input
                                v-model.number="form.order"
                                type="number"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p class="text-xs text-gray-500 mt-1">Menor número = aparece primero</p>
                        </div>

                        <!-- Activa -->
                        <div class="flex items-center gap-3">
                            <input
                                v-model="form.active"
                                type="checkbox"
                                id="active"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <label for="active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Categoría activa
                            </label>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Guardando...' : (editingCategory ? 'Actualizar' : 'Crear') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
