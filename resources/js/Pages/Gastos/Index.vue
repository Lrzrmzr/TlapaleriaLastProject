<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
const props = defineProps({ gastos: Array, user: Object });

const form = useForm({
  descripcion: '',
  total: 0,
});

function submit() {
  form.post(route('gastos.store'), {
    onSuccess: () => form.reset(),
  });
}

function destroy(id) {
  router.delete(route('gastos.destroy', id));
}
</script>
<template>
  <div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Gastos</h1>
    <form @submit.prevent="submit" class="mb-8 bg-white p-4 rounded shadow">
      <div class="mb-4">
        <label class="block mb-1 font-semibold">Descripción</label>
        <input v-model="form.descripcion" type="text" class="w-full border rounded px-3 py-2" required />
      </div>
      <div class="mb-4">
        <label class="block mb-1 font-semibold">Total</label>
        <input v-model="form.total" type="number" step="0.01" class="w-full border rounded px-3 py-2" required />
      </div>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Agregar</button>
    </form>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded shadow">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2">Descripción</th>
            <th class="px-4 py-2">Total</th>
            <th class="px-4 py-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="gasto in gastos" :key="gasto.id">
            <td class="px-4 py-2 break-words max-w-xs">{{ gasto.descripcion }}</td>
            <td class="px-4 py-2">{{ gasto.total }}</td>
            <td class="px-4 py-2 flex gap-2 flex-wrap">
              <button v-if="user.role === 'admin'" @click="destroy(gasto.id)" class="bg-red-600 text-white px-2 py-1 rounded">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
