<script setup>
import { ref, computed } from 'vue';
import { router, useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    cuentas:   Array,
    stats:     Object,
    customers: Array,
    branches:  Array,
    filters:   Object,
    user:      Object,
});

// ─── State ────────────────────────────────────────────────────────────────────

const mostrarFormulario   = ref(false);
const cuentaEditando      = ref(null);
const cuentaExpandida     = ref(null);
const mostrarFormCobro    = ref(null);
const buscarCliente       = ref('');
const clienteNuevo        = ref(false); // toggle: seleccionar existente vs crear nuevo

// Filters
const filtros = ref({
    status:       props.filters?.status      ?? '',
    customer_id:  props.filters?.customer_id ?? '',
    fecha_desde:  props.filters?.fecha_desde ?? '',
    fecha_hasta:  props.filters?.fecha_hasta ?? '',
    search:       props.filters?.search      ?? '',
});

// ─── Forms ────────────────────────────────────────────────────────────────────

const form = useForm({
    customer_id:       '',
    customer_name:     '',
    customer_phone:    '',
    branch_id:         props.branches?.[0]?.id ?? '',
    concepto:          '',
    monto_total:       '',
    fecha:             new Date().toISOString().slice(0, 10),
    fecha_vencimiento: '',
    notas:             '',
});

const formCobro = useForm({
    monto:        '',
    fecha_cobro:  new Date().toISOString().slice(0, 10),
    metodo_pago:  'efectivo',
    referencia:   '',
    notas:        '',
});

// ─── Computed ─────────────────────────────────────────────────────────────────

const canDelete = computed(() => props.user?.canDelete ?? false);
const canCreate = computed(() => props.user?.canCreate ?? false);
const canEdit   = computed(() => props.user?.canEdit   ?? false);

const clientesFiltrados = computed(() => {
    if (!buscarCliente.value) return props.customers.slice(0, 20);
    const q = buscarCliente.value.toLowerCase();
    return props.customers.filter(c =>
        c.name.toLowerCase().includes(q) ||
        (c.phone && c.phone.includes(q))
    ).slice(0, 20);
});

const cuentasFiltradas = computed(() => {
    let result = [...props.cuentas];
    if (filtros.value.status)      result = result.filter(c => c.status === filtros.value.status);
    if (filtros.value.customer_id) result = result.filter(c => c.customer?.id == filtros.value.customer_id);
    if (filtros.value.search) {
        const q = filtros.value.search.toLowerCase();
        result = result.filter(c =>
            c.customer?.name?.toLowerCase().includes(q) ||
            c.concepto?.toLowerCase().includes(q)
        );
    }
    return result;
});

// ─── Methods ──────────────────────────────────────────────────────────────────

function aplicarFiltros() {
    router.get(route('cuentas-por-cobrar.index'), filtros.value, { preserveState: true, replace: true });
}

function limpiarFiltros() {
    filtros.value = { status: '', customer_id: '', fecha_desde: '', fecha_hasta: '', search: '' };
    router.get(route('cuentas-por-cobrar.index'));
}

function submitNueva() {
    form.post(route('cuentas-por-cobrar.store'), {
        onSuccess: () => {
            form.reset();
            mostrarFormulario.value = false;
            clienteNuevo.value = false;
        },
    });
}

function abrirEdicion(cuenta) {
    cuentaEditando.value  = cuenta.id;
    form.customer_id      = cuenta.customer?.id ?? '';
    form.branch_id        = cuenta.branch?.id ?? '';
    form.concepto         = cuenta.concepto;
    form.monto_total      = cuenta.monto_total;
    form.fecha            = cuenta.fecha;
    form.fecha_vencimiento= cuenta.fecha_vencimiento ?? '';
    form.notas            = cuenta.notas ?? '';
    clienteNuevo.value    = false;
    mostrarFormulario.value = true;
}

function submitEdicion(id) {
    form.put(route('cuentas-por-cobrar.update', id), {
        onSuccess: () => {
            form.reset();
            cuentaEditando.value = null;
            mostrarFormulario.value = false;
        },
    });
}

function cancelarForm() {
    form.reset();
    cuentaEditando.value = null;
    mostrarFormulario.value = false;
    clienteNuevo.value = false;
}

function destroy(id) {
    if (confirm('¿Eliminar esta cuenta por cobrar? Esta acción no se puede deshacer.')) {
        router.delete(route('cuentas-por-cobrar.destroy', id));
    }
}

function toggleExpand(id) {
    cuentaExpandida.value = cuentaExpandida.value === id ? null : id;
}

function abrirFormCobro(id) {
    mostrarFormCobro.value = mostrarFormCobro.value === id ? null : id;
    formCobro.reset();
    formCobro.fecha_cobro   = new Date().toISOString().slice(0, 10);
    formCobro.metodo_pago   = 'efectivo';
}

function submitCobro(cuentaId) {
    formCobro.post(route('cuentas-por-cobrar.cobros.store', cuentaId), {
        onSuccess: () => {
            formCobro.reset();
            mostrarFormCobro.value = null;
        },
    });
}

function destroyCobro(cobroId) {
    if (confirm('¿Eliminar este cobro?')) {
        router.delete(route('cuentas-por-cobrar.cobros.destroy', cobroId));
    }
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr + 'T12:00:00').toLocaleDateString('es-MX', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
}

const statusColors = {
    pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    parcial:   'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    cobrado:   'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
};
</script>

<template>
    <Head title="Cuentas por Cobrar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Cuentas por Cobrar
            </h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Por cobrar</p>
                    <p class="mt-1 text-2xl font-bold text-orange-600 dark:text-orange-400">{{ formatCurrency(stats.total_por_cobrar) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total cobrado</p>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(stats.total_cobrado) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cuentas vencidas</p>
                    <p class="mt-1 text-2xl font-bold" :class="stats.cuentas_vencidas > 0 ? 'text-red-600' : 'text-gray-700 dark:text-gray-200'">
                        {{ stats.cuentas_vencidas }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Clientes con deuda</p>
                    <p class="mt-1 text-2xl font-bold text-gray-700 dark:text-gray-200">{{ stats.clientes_con_deuda }}</p>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap gap-2">
                    <!-- Search -->
                    <input v-model="filtros.search" @input="aplicarFiltros" type="text"
                        placeholder="Buscar cliente..."
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 w-48" />

                    <select v-model="filtros.status" @change="aplicarFiltros"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="parcial">Parcial</option>
                        <option value="cobrado">Cobrado</option>
                    </select>

                    <button @click="limpiarFiltros" v-if="filtros.status || filtros.search || filtros.fecha_desde || filtros.fecha_hasta"
                        class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg">
                        Limpiar
                    </button>
                </div>

                <button v-if="canCreate" @click="mostrarFormulario = !mostrarFormulario; cuentaEditando = null"
                    class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-semibold rounded-lg hover:bg-orange-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nueva venta a crédito
                </button>
            </div>

            <!-- Form Nueva / Edición -->
            <div v-if="mostrarFormulario" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                    {{ cuentaEditando ? 'Editar cuenta por cobrar' : 'Registrar venta a crédito' }}
                </h3>

                <form @submit.prevent="cuentaEditando ? submitEdicion(cuentaEditando) : submitNueva()">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                        <!-- Cliente: seleccionar existente o crear nuevo -->
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente *</label>

                            <!-- Toggle modo -->
                            <div v-if="!cuentaEditando" class="flex gap-3 mb-2">
                                <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                    <input type="radio" :value="false" v-model="clienteNuevo" class="text-blue-600" />
                                    <span class="text-gray-700 dark:text-gray-300">Seleccionar existente</span>
                                </label>
                                <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                    <input type="radio" :value="true" v-model="clienteNuevo" class="text-blue-600" />
                                    <span class="text-gray-700 dark:text-gray-300">Nuevo cliente</span>
                                </label>
                            </div>

                            <!-- Seleccionar existente -->
                            <div v-if="!clienteNuevo">
                                <input v-model="buscarCliente" type="text" placeholder="Buscar cliente..."
                                    class="w-full rounded-t-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" />
                                <select v-model="form.customer_id" :required="!clienteNuevo && !cuentaEditando" size="4"
                                    class="w-full rounded-b-lg border-t-0 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="">— Sin cliente seleccionado —</option>
                                    <option v-for="c in clientesFiltrados" :key="c.id" :value="c.id">
                                        {{ c.name }} {{ c.phone ? `· ${c.phone}` : '' }}
                                    </option>
                                </select>
                            </div>

                            <!-- Nuevo cliente -->
                            <div v-else class="grid grid-cols-2 gap-2">
                                <input v-model="form.customer_name" type="text" required placeholder="Nombre del cliente *"
                                    class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" />
                                <input v-model="form.customer_phone" type="text" placeholder="Teléfono (opcional)"
                                    class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" />
                            </div>

                            <p v-if="form.errors.customer_id || form.errors.customer_name"
                                class="text-xs text-red-600 mt-1">{{ form.errors.customer_id || form.errors.customer_name }}</p>
                        </div>

                        <div v-if="user.isRoot && branches.length">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sucursal *</label>
                            <select v-model="form.branch_id" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Concepto *</label>
                            <input v-model="form.concepto" type="text" required placeholder="Descripción de la venta a crédito"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" />
                            <p v-if="form.errors.concepto" class="text-xs text-red-600 mt-1">{{ form.errors.concepto }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto total *</label>
                            <input v-model="form.monto_total" type="number" step="0.01" min="0.01" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="0.00" />
                            <p v-if="form.errors.monto_total" class="text-xs text-red-600 mt-1">{{ form.errors.monto_total }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha *</label>
                            <input v-model="form.fecha" type="date" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha límite de pago</label>
                            <input v-model="form.fecha_vencimiento" type="date"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas internas</label>
                            <textarea v-model="form.notas" rows="2"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="Observaciones opcionales..." />
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-3">
                        <button type="button" @click="cancelarForm"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition disabled:opacity-50 text-sm font-semibold">
                            {{ form.processing ? 'Guardando...' : (cuentaEditando ? 'Actualizar' : 'Registrar') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de cuentas -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div v-if="cuentasFiltradas.length === 0" class="py-16 text-center text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-2">No hay cuentas por cobrar registradas</p>
                </div>

                <div v-for="cuenta in cuentasFiltradas" :key="cuenta.id"
                    class="border-b border-gray-100 dark:border-gray-700 last:border-0">

                    <!-- Fila principal -->
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ cuenta.customer?.name ?? '—' }}
                                    </span>
                                    <span v-if="cuenta.customer?.phone"
                                        class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ cuenta.customer.phone }}
                                    </span>
                                    <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', statusColors[cuenta.status]]">
                                        {{ cuenta.status }}
                                    </span>
                                    <span v-if="cuenta.vencida"
                                        class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                        vencida
                                    </span>
                                </div>
                                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300 truncate">{{ cuenta.concepto }}</p>
                                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                    {{ formatDate(cuenta.fecha) }}
                                    <span v-if="cuenta.fecha_vencimiento"> · Vence: {{ formatDate(cuenta.fecha_vencimiento) }}</span>
                                    <span v-if="cuenta.branch"> · {{ cuenta.branch.name }}</span>
                                </p>
                            </div>

                            <!-- Montos -->
                            <div class="text-right shrink-0">
                                <p class="text-lg font-bold" :class="cuenta.saldo > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400'">
                                    {{ formatCurrency(cuenta.saldo) }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">saldo de {{ formatCurrency(cuenta.monto_total) }}</p>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div class="mt-3 flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="bg-orange-500 h-1.5 rounded-full transition-all"
                                    :style="{ width: Math.min(100, (cuenta.monto_cobrado / cuenta.monto_total) * 100) + '%' }" />
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">
                                {{ Math.round((cuenta.monto_cobrado / cuenta.monto_total) * 100) }}% cobrado
                            </span>
                        </div>

                        <!-- Action buttons -->
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button @click="toggleExpand(cuenta.id)"
                                class="text-xs px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                {{ cuentaExpandida === cuenta.id ? 'Ocultar cobros' : `Ver cobros (${cuenta.cobros.length})` }}
                            </button>

                            <button v-if="cuenta.status !== 'cobrado'" @click="abrirFormCobro(cuenta.id)"
                                class="text-xs px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition font-semibold">
                                Registrar cobro
                            </button>

                            <button v-if="canEdit" @click="abrirEdicion(cuenta)"
                                class="text-xs px-3 py-1.5 border border-blue-300 dark:border-blue-600 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                                Editar
                            </button>

                            <button v-if="canDelete" @click="destroy(cuenta.id)"
                                class="text-xs px-3 py-1.5 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                Eliminar
                            </button>
                        </div>
                    </div>

                    <!-- Form de cobro inline -->
                    <div v-if="mostrarFormCobro === cuenta.id"
                        class="px-4 pb-4 bg-orange-50 dark:bg-orange-900/10 border-t border-orange-100 dark:border-orange-900/30">
                        <h4 class="text-sm font-semibold text-orange-800 dark:text-orange-400 pt-3 mb-3">Registrar cobro — saldo: {{ formatCurrency(cuenta.saldo) }}</h4>
                        <form @submit.prevent="submitCobro(cuenta.id)">
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Monto *</label>
                                    <input v-model="formCobro.monto" type="number" step="0.01" :max="cuenta.saldo" min="0.01" required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Fecha *</label>
                                    <input v-model="formCobro.fecha_cobro" type="date" required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Método</label>
                                    <select v-model="formCobro.metodo_pago"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm">
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Referencia</label>
                                    <input v-model="formCobro.referencia" type="text" placeholder="Folio, ref. transferencia..."
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm" />
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button type="submit" :disabled="formCobro.processing"
                                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-xs font-semibold disabled:opacity-50 transition">
                                    {{ formCobro.processing ? 'Guardando...' : 'Guardar cobro' }}
                                </button>
                                <button type="button" @click="mostrarFormCobro = null"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-xs transition">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Historial de cobros -->
                    <div v-if="cuentaExpandida === cuenta.id && cuenta.cobros.length > 0"
                        class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider pt-3 mb-2">Historial de cobros</h4>
                        <div class="space-y-2">
                            <div v-for="cobro in cuenta.cobros" :key="cobro.id"
                                class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2">
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ formatCurrency(cobro.monto) }}</span>
                                    <span class="ml-2 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(cobro.fecha_cobro) }}</span>
                                    <span class="ml-2 text-xs px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 capitalize">{{ cobro.metodo_pago }}</span>
                                    <span v-if="cobro.referencia" class="ml-1 text-xs text-gray-400">· {{ cobro.referencia }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">{{ cobro.user?.name }}</span>
                                    <button v-if="canDelete" @click="destroyCobro(cobro.id)"
                                        class="text-red-400 hover:text-red-600 transition p-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
