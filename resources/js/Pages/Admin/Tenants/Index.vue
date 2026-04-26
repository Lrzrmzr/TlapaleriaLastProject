<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tenants: Array,
});

// ─── Estado ───────────────────────────────────────────────────────────────────
const showModal = ref(false);
const editingTenant = ref(null);
const busqueda = ref('');

// ─── Form ─────────────────────────────────────────────────────────────────────
const form = useForm({
    id:            '',
    name:          '',
    rfc:           '',
    email:         '',
    phone:         '',
    plan:          'starter',
    status:        'trial',
    trial_ends_at: '',
});

// ─── Computed ─────────────────────────────────────────────────────────────────
const tenantsFiltrados = computed(() => {
    if (!busqueda.value) return props.tenants;
    const q = busqueda.value.toLowerCase();
    return props.tenants.filter(t =>
        t.name.toLowerCase().includes(q) ||
        t.id.toLowerCase().includes(q) ||
        (t.email && t.email.toLowerCase().includes(q))
    );
});

const statusLabel = {
    trial:     'Trial',
    active:    'Activo',
    suspended: 'Suspendido',
    cancelled: 'Cancelado',
};

const statusClass = {
    trial:     'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    active:    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    suspended: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    cancelled: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
};

const planLabel = {
    free:       'Free',
    starter:    'Starter',
    pro:        'Pro',
    enterprise: 'Enterprise',
};

// ─── Funciones ────────────────────────────────────────────────────────────────
function openModal(tenant = null) {
    if (tenant) {
        editingTenant.value = tenant;
        form.id            = tenant.id;
        form.name          = tenant.name;
        form.rfc           = tenant.rfc || '';
        form.email         = tenant.email || '';
        form.phone         = tenant.phone || '';
        form.plan          = tenant.plan || 'starter';
        form.status        = tenant.status;
        form.trial_ends_at = tenant.trial_ends_at || '';
    } else {
        editingTenant.value = null;
        form.reset();
        form.plan   = 'starter';
        form.status = 'trial';
    }
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingTenant.value = null;
    form.reset();
}

function submit() {
    if (editingTenant.value) {
        form.put(route('admin.tenants.update', editingTenant.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.tenants.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function toggleSuspend(tenant) {
    const action = tenant.status === 'suspended' ? 'reactivar' : 'suspender';
    if (!confirm(`¿Estás seguro de ${action} la empresa "${tenant.name}"?`)) return;
    router.post(route('admin.tenants.suspend', tenant.id));
}

function impersonate(tenant) {
    if (!confirm(`¿Ingresar como administrador de "${tenant.name}"?`)) return;
    router.post(route('admin.tenants.impersonate', tenant.id));
}
</script>

<template>
    <Head title="Super Admin — Empresas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                        Gestión de Empresas
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Panel Super Admin — todas las empresas del sistema
                    </p>
                </div>
                <button
                    @click="openModal()"
                    class="bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Empresa
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Flash message -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl text-green-800 dark:text-green-300 text-sm"
                >
                    {{ $page.props.flash.success }}
                </div>

                <!-- Búsqueda + stats -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="flex-1 w-full">
                            <input
                                v-model="busqueda"
                                type="text"
                                placeholder="Buscar por nombre, slug o email..."
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-violet-500 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            <span>Total: <strong class="text-gray-900 dark:text-white">{{ tenants.length }}</strong></span>
                            <span>Activos: <strong class="text-green-600 dark:text-green-400">{{ tenants.filter(t => t.status === 'active').length }}</strong></span>
                            <span>Trial: <strong class="text-yellow-600 dark:text-yellow-400">{{ tenants.filter(t => t.status === 'trial').length }}</strong></span>
                            <span>Suspendidos: <strong class="text-red-600 dark:text-red-400">{{ tenants.filter(t => t.status === 'suspended').length }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Empresa</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contacto</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuarios</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sucursales</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Creado</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="tenant in tenantsFiltrados"
                                :key="tenant.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                :class="tenant.status === 'suspended' ? 'opacity-60' : ''"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ tenant.name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ tenant.id }}</div>
                                    <div v-if="tenant.rfc" class="text-xs text-gray-500 dark:text-gray-400">RFC: {{ tenant.rfc }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div>{{ tenant.email }}</div>
                                    <div v-if="tenant.phone" class="text-xs text-gray-500">{{ tenant.phone }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-violet-100 text-violet-800 dark:bg-violet-900/30 dark:text-violet-300">
                                        {{ planLabel[tenant.plan] ?? tenant.plan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2 py-1 text-xs font-semibold rounded-full', statusClass[tenant.status]]">
                                        {{ statusLabel[tenant.status] ?? tenant.status }}
                                    </span>
                                    <div v-if="tenant.trial_ends_at && tenant.status === 'trial'" class="text-xs text-gray-500 mt-1">
                                        Expira: {{ tenant.trial_ends_at }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-900 dark:text-white font-semibold">
                                    {{ tenant.users_count }}
                                </td>
                                <td class="px-6 py-4 text-center text-gray-900 dark:text-white font-semibold">
                                    {{ tenant.branches_count }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ tenant.created_at }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Editar -->
                                        <button
                                            @click="openModal(tenant)"
                                            class="px-3 py-1.5 bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400 rounded-lg text-xs font-semibold transition-colors"
                                        >
                                            Editar
                                        </button>
                                        <!-- Suspender / Activar -->
                                        <button
                                            @click="toggleSuspend(tenant)"
                                            :class="[
                                                'px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors',
                                                tenant.status === 'suspended'
                                                    ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400'
                                            ]"
                                        >
                                            {{ tenant.status === 'suspended' ? 'Activar' : 'Suspender' }}
                                        </button>
                                        <!-- Impersonar -->
                                        <button
                                            @click="impersonate(tenant)"
                                            class="px-3 py-1.5 bg-violet-100 text-violet-700 hover:bg-violet-200 dark:bg-violet-900/30 dark:text-violet-400 rounded-lg text-xs font-semibold transition-colors"
                                            :disabled="tenant.status === 'suspended'"
                                        >
                                            Ingresar
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="tenantsFiltrados.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <p class="text-lg font-medium">No se encontraron empresas</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Crear / Editar -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl">
                    <form @submit.prevent="submit">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-violet-500 to-purple-600 px-6 py-4 rounded-t-2xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-bold text-white">
                                    {{ editingTenant ? 'Editar Empresa' : 'Nueva Empresa' }}
                                </h3>
                                <button type="button" @click="closeModal" class="text-white hover:text-gray-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="p-6 grid grid-cols-2 gap-4">
                            <!-- Slug / ID (solo en creación) -->
                            <div v-if="!editingTenant" class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Slug (subdominio) *
                                    <span class="text-xs text-gray-400 font-normal ml-1">solo letras minúsculas, números y guiones</span>
                                </label>
                                <input
                                    v-model="form.id"
                                    type="text"
                                    required
                                    pattern="[a-z0-9\-]+"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-violet-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="ferreteria-garcia"
                                />
                                <p v-if="form.errors.id" class="text-red-500 text-xs mt-1">{{ form.errors.id }}</p>
                            </div>
                            <div v-else class="col-span-2 text-sm text-gray-500 dark:text-gray-400">
                                Slug: <span class="font-mono font-semibold text-gray-700 dark:text-gray-200">{{ editingTenant.id }}</span>
                            </div>

                            <!-- Nombre -->
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre de la empresa *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-violet-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Ferretería García S.A. de C.V."
                                />
                                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                            </div>

                            <!-- RFC -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RFC</label>
                                <input
                                    v-model="form.rfc"
                                    type="text"
                                    maxlength="13"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-violet-500 dark:bg-gray-700 dark:text-white uppercase"
                                    placeholder="GARJ800101XXX"
                                />
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-violet-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="555-1234"
                                />
                            </div>

                            <!-- Email -->
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-violet-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="admin@ferreteria.com"
                                />
                                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                            </div>

                            <!-- Plan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan *</label>
                                <select
                                    v-model="form.plan"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-violet-500 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="free">Free</option>
                                    <option value="starter">Starter</option>
                                    <option value="pro">Pro</option>
                                    <option value="enterprise">Enterprise</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado *</label>
                                <select
                                    v-model="form.status"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-violet-500 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="trial">Trial</option>
                                    <option value="active">Activo</option>
                                    <option value="suspended">Suspendido</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>

                            <!-- Trial ends at -->
                            <div class="col-span-2" v-if="form.status === 'trial'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vencimiento del trial</label>
                                <input
                                    v-model="form.trial_ends_at"
                                    type="date"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:ring-2 focus:ring-violet-500 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end gap-3 rounded-b-2xl">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2 bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50"
                            >
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>{{ editingTenant ? 'Actualizar' : 'Crear Empresa' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
