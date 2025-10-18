<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 via-blue-100 to-gray-200">
            <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-2xl border border-gray-100">
                <div class="flex flex-col items-center mb-8">
                    <!-- Logo moderno, puedes reemplazar por una imagen -->
                    <svg class="w-16 h-16 mb-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="white" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8M12 8v8" /></svg>
                    <h1 class="text-3xl font-extrabold text-gray-800 mb-2 tracking-tight">Tlapalería TOTORO</h1>
                    <p class="text-base text-gray-500">Accede a tu cuenta</p>
                </div>
                <Head title="Iniciar sesión" />
                <div v-if="status" class="mb-4 text-sm font-medium text-blue-600">
                    {{ status }}
                </div>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-700 font-semibold" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-2 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 text-gray-800"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel for="password" value="Password" class="text-gray-700 font-semibold" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-2 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 text-gray-800"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" />
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
                    <PrimaryButton
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all duration-200"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Ingresar
                    </PrimaryButton>
                </form>
                <div class="mt-8 text-center text-xs text-gray-400">
                    © 2025 Tlapalería TOTORO
                </div>
            </div>
        </div>
</template>
