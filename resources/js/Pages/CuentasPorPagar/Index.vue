<script setup>
import { ref, computed } from 'vue';
import { router, useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    cuentas:   Array,
    stats:     Object,
    suppliers: Array,
    branches:  Array,
    filters:   Object,
    user:      Object,
});

// ─── State ────────────────────────────────────────────────────────────────────

const mostrarFormulario   = ref(false);
const cuentaEditando      = ref(null);
const cuentaExpandida     = ref(null);
const mostrarFormPago     = ref(null); // id de cuenta donde se abre el form de pago

// Filters
const filtros = ref({
    status:       props.filters?.status       ?? '',
    supplier_id:  props.filters?.supplier_id  ?? '',
    fecha_desde:  props.filters?.fecha_desde  ?? '',
    fecha_hasta:  props.filters?.fecha_hasta  ?? '',
});

// ─── Forms ────────────────────────────────────────────────────────────────────

const form = useForm({
    supplier_id:       '',
    branch_id:         props.branches?.[0]?.id ?? '',
    numero_nota:       '',
    concepto:          '',
    monto_total:       '',
    fecha_nota:        new Date().toISOString().slice(0, 10),
    fecha_vencimiento: '',
    notas:             '',
});

const formPago = useForm({
    monto:       '',
    fecha_pago:  new Date().toISOString().slice(0, 10),
    metodo_pago: 'efectivo',
    referencia:  '',
    notas:       '',
});

// ─── Computed ─────────────────────────────────────────────────────────────────

const canDelete = computed(() => props.user?.canDelete ?? false);
const canCreate = computed(() => props.user?.canCreate ?? false);
const canEdit   = computed(() => props.user?.canEdit   ?? false);

const cuentasFiltradas = computed(() => {
    let result = [...props.cuentas];
    if (filtros.value.status)      result = result.filter(c => c.status === filtros.value.status);
    if (filtros.value.supplier_id) result = result.filter(c => c.supplier?.id == filtros.value.supplier_id);
    return result;
});

// ─── Methods ──────────────────────────────────────────────────────────────────

function aplicarFiltros() {
    router.get(route('cuentas-por-pagar.index'), filtros.value, { preserveState: true, replace: true });
}

function limpiarFiltros() {
    filtros.value = { status: '', supplier_id: '', fecha_desde: '', fecha_hasta: '' };
    router.get(route('cuentas-por-pagar.index'));
}

function submitNueva() {
    form.post(route('cuentas-por-pagar.store'), {
        onSuccess: () => {
            form.reset();
            mostrarFormulario.value = false;
        },
    });
}

function abrirEdicion(cuenta) {
    cuentaEditando.value = cuenta.id;
    form.supplier_id       = cuenta.supplier?.id ?? '';
    form.branch_id         = cuenta.branch?.id ?? '';
    form.numero_nota       = cuenta.numero_nota ?? '';
    form.concepto          = cuenta.concepto;
    form.monto_total       = cuenta.monto_total;
    form.fecha_nota        = cuenta.fecha_nota;
    form.fecha_vencimiento = cuenta.fecha_vencimiento ?? '';
    form.notas             = cuenta.notas ?? '';
    mostrarFormulario.value = true;
}

function submitEdicion(id) {
    form.put(route('cuentas-por-pagar.update', id), {
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
}

function destroy(id) {
    if (confirm('¿Eliminar esta cuenta por pagar? Esta acción no se puede deshacer.')) {
        router.delete(route('cuentas-por-pagar.destroy', id));
    }
}

function toggleExpand(id) {
    cuentaExpandida.value = cuentaExpandida.value === id ? null : id;
}

function abrirFormPago(id) {
    mostrarFormPago.value = mostrarFormPago.value === id ? null : id;
    formPago.reset();
    formPago.fecha_pago = new Date().toISOString().slice(0, 10);
    formPago.metodo_pago = 'efectivo';
}

function submitPago(cuentaId) {
    formPago.post(route('cuentas-por-pagar.pagos.store', cuentaId), {
        onSuccess: () => {
            formPago.reset();
            mostrarFormPago.value = null;
        },
    });
}

function destroyPago(pagoId) {
    if (confirm('¿Eliminar este pago?')) {
        router.delete(route('cuentas-por-pagar.pagos.destroy', pagoId));
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
    pagado:    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
};
</script>

<template>
    <Head title="Cuentas por Pagar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Cuentas por Pagar
            </h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Deuda total</p>
                    <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ formatCurrency(stats.total_deuda) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total pagado</p>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(stats.total_pagado) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cuentas vencidas</p>
                    <p class="mt-1 text-2xl font-bold" :class="stats.cuentas_vencidas > 0 ? 'text-red-600' : 'text-gray-700 dark:text-gray-200'">
                        {{ stats.cuentas_vencidas }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Proveedores con deuda</p>
                    <p class="mt-1 text-2xl font-bold text-gray-700 dark:text-gray-200">{{ stats.proveedores_con_deuda }}</p>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <!-- Filters -->
                <div class="flex flex-wrap gap-2">
                    <select v-model="filtros.status" @change="aplicarFiltros"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="parcial">Parcial</option>
                        <option value="pagado">Pagado</option>
                    </select>

                    <select v-model="filtros.supplier_id" @change="aplicarFiltros"
                        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los proveedores</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>

                    <button @click="limpiarFiltros" v-if="filtros.status || filtros.supplier_id || filtros.fecha_desde || filtros.fecha_hasta"
                        class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg">
                        Limpiar filtros
                    </button>
                </div>

                <button v-if="canCreate" @click="mostrarFormulario = !mostrarFormulario; cuentaEditando = null"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nueva nota
                </button>
            </div>

            <!-- Form Nueva / Edición -->
            <div v-if="mostrarFormulario" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                    {{ cuentaEditando ? 'Editar cuenta por pagar' : 'Registrar nota de proveedor' }}
                </h3>

                <form @submit.prevent="cuentaEditando ? submitEdicion(cuentaEditando) : submitNueva()">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Proveedor *</label>
                            <select v-model="form.supplier_id" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Selecciona proveedor</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <p v-if="form.errors.supplier_id" class="text-xs text-red-600 mt-1">{{ form.errors.supplier_id }}</p>
                        </div>

                        <div v-if="user.isRoot && branches.length">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sucursal *</label>
                            <select v-model="form.branch_id" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° de nota / factura</label>
                            <input v-model="form.numero_nota" type="text" placeholder="Ej: F-00123"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Concepto *</label>
                            <input v-model="form.concepto" type="text" required placeholder="Descripción de la deuda con el proveedor"
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de nota *</label>
                            <input v-model="form.fecha_nota" type="date" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de vencimiento</label>
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
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 text-sm font-semibold">
                            {{ form.processing ? 'Guardando...' : (cuentaEditando ? 'Actualizar' : 'Registrar') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de cuentas -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div v-if="cuentasFiltradas.length === 0" class="py-16 text-center text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2">No hay cuentas por pagar registradas</p>
                </div>

                <div v-for="cuenta in cuentasFiltradas" :key="cuenta.id"
                    class="border-b border-gray-100 dark:border-gray-700 last:border-0">

                    <!-- Fila principal -->
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ cuenta.supplier?.name ?? '—' }}</span>
                                    <span v-if="cuenta.numero_nota" class="text-xs text-gray-500 dark:text-gray-400 font-mono bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                        {{ cuenta.numero_nota }}
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
                                    {{ formatDate(cuenta.fecha_nota) }}
                                    <span v-if="cuenta.fecha_vencimiento"> · Vence: {{ formatDate(cuenta.fecha_vencimiento) }}</span>
                                    <span v-if="cuenta.branch"> · {{ cuenta.branch.name }}</span>
                                </p>
                            </div>

                            <!-- Montos -->
                            <div class="text-right shrink-0">
                                <p class="text-lg font-bold" :class="cuenta.saldo > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                    {{ formatCurrency(cuenta.saldo) }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">saldo de {{ formatCurrency(cuenta.monto_total) }}</p>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div class="mt-3 flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="bg-green-500 h-1.5 rounded-full transition-all"
                                    :style="{ width: Math.min(100, (cuenta.monto_pagado / cuenta.monto_total) * 100) + '%' }" />
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">
                                {{ Math.round((cuenta.monto_pagado / cuenta.monto_total) * 100) }}% pagado
                            </span>
                        </div>

                        <!-- Action buttons -->
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button @click="toggleExpand(cuenta.id)"
                                class="text-xs px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                {{ cuentaExpandida === cuenta.id ? 'Ocultar pagos' : `Ver pagos (${cuenta.pagos.length})` }}
                            </button>

                            <button v-if="cuenta.status !== 'pagado'" @click="abrirFormPago(cuenta.id)"
                                class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                                Registrar pago
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

                    <!-- Form de pago inline -->
                    <div v-if="mostrarFormPago === cuenta.id"
                        class="px-4 pb-4 bg-green-50 dark:bg-green-900/10 border-t border-green-100 dark:border-green-900/30">
                        <h4 class="text-sm font-semibold text-green-800 dark:text-green-400 pt-3 mb-3">Registrar pago — saldo: {{ formatCurrency(cuenta.saldo) }}</h4>
                        <form @submit.prevent="submitPago(cuenta.id)">
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Monto *</label>
                                    <input v-model="formPago.monto" type="number" step="0.01" :max="cuenta.saldo" min="0.01" required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-green-500 focus:ring-green-500 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Fecha *</label>
                                    <input v-model="formPago.fecha_pago" type="date" required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-green-500 focus:ring-green-500 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Método</label>
                                    <select v-model="formPago.metodo_pago"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Referencia</label>
                                    <input v-model="formPago.referencia" type="text" placeholder="Folio, N° cheque..."
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-green-500 focus:ring-green-500 text-sm" />
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button type="submit" :disabled="formPago.processing"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xs font-semibold disabled:opacity-50 transition">
                                    {{ formPago.processing ? 'Guardando...' : 'Guardar pago' }}
                                </button>
                                <button type="button" @click="mostrarFormPago = null"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-xs transition">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Historial de pagos -->
                    <div v-if="cuentaExpandida === cuenta.id && cuenta.pagos.length > 0"
                        class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider pt-3 mb-2">Historial de pagos</h4>
                        <div class="space-y-2">
                            <div v-for="pago in cuenta.pagos" :key="pago.id"
                                class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2">
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ formatCurrency(pago.monto) }}</span>
                                    <span class="ml-2 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(pago.fecha_pago) }}</span>
                                    <span class="ml-2 text-xs px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 capitalize">{{ pago.metodo_pago }}</span>
                                    <span v-if="pago.referencia" class="ml-1 text-xs text-gray-400">· {{ pago.referencia }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">{{ pago.user?.name }}</span>
                                    <button v-if="canDelete" @click="destroyPago(pago.id)"
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
