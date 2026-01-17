<script setup>
import { ref } from 'vue';
import { useAuth } from '@/composables/useAuth';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const { login } = useAuth();

// Estado del formulario
const form = ref({
    email: '',
    password: '',
    remember: false,
});

const errors = ref({
    email: '',
    password: '',
    general: '',
});

const processing = ref(false);

const submit = async () => {
    // Limpiar errores
    errors.value = { email: '', password: '', general: '' };
    processing.value = true;

    try {
        // Login con OAuth 2.0
        await login({
            email: form.value.email,
            password: form.value.password,
        });

        // Forzar recarga completa para que la sesión web se active
        window.location.href = '/dashboard';

    } catch (error) {
        processing.value = false;

        if (error.response) {
            // Errores del servidor
            if (error.response.status === 422) {
                // Errores de validación
                errors.value = error.response.data.errors || {};
            } else if (error.response.status === 401) {
                // Credenciales incorrectas
                errors.value.general = error.response.data.message || 'Credenciales incorrectas';
            } else if (error.response.status === 403) {
                // Sin permisos
                errors.value.general = error.response.data.message || 'No tienes permisos para acceder';
            } else {
                errors.value.general = 'Error al iniciar sesión. Por favor, intenta de nuevo.';
            }
        } else {
            errors.value.general = 'Error de conexión. Verifica tu conexión a internet.';
        }

        // Limpiar contraseña en caso de error
        form.value.password = '';
    }
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 via-blue-100 to-gray-200">
        <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="flex flex-col items-center mb-8">
                <!-- Logo moderno -->
                <svg class="w-16 h-16 mb-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="white" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8M12 8v8" />
                </svg>
                <h1 class="text-3xl font-extrabold text-gray-800 mb-2 tracking-tight">Tlapalería TOTORO</h1>
                <p class="text-base text-gray-500">Accede a tu cuenta</p>
            </div>

            <Head title="Iniciar sesión" />

            <!-- Mensaje de estado -->
            <div v-if="status" class="mb-4 text-sm font-medium text-blue-600 bg-blue-50 p-3 rounded-lg">
                {{ status }}
            </div>

            <!-- Error general -->
            <div v-if="errors.general" class="mb-4 text-sm font-medium text-red-600 bg-red-50 p-3 rounded-lg">
                {{ errors.general }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Email" class="text-gray-700 font-semibold" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-2 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 text-gray-800"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        :disabled="processing"
                    />
                    <InputError class="mt-2" :message="errors.email" />
                </div>

                <!-- Password -->
                <div>
                    <InputLabel for="password" value="Contraseña" class="text-gray-700 font-semibold" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-2 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 text-gray-800"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        :disabled="processing"
                    />
                    <InputError class="mt-2" :message="errors.password" />
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" :disabled="processing" />
                        <span class="ms-2 text-sm text-gray-600">Recordarme</span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-blue-600 underline hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>

                <!-- Submit button -->
                <div class="flex items-center justify-end">
                    <PrimaryButton
                        class="w-full justify-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-xl py-3 transition-all duration-300"
                        :class="{ 'opacity-50': processing }"
                        :disabled="processing"
                    >
                        <span v-if="!processing">Iniciar sesión</span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Iniciando sesión...
                        </span>
                    </PrimaryButton>
                </div>
            </form>

            <!-- Indicador OAuth 2.0 -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    🔐 Autenticación segura con OAuth 2.0
                </p>
            </div>
        </div>
    </div>
</template>
