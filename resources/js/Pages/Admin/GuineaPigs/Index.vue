<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    guineaPigs: Array,
    trashedPigs: Array,
    lowStockCount: Number
});

// Control de pestañas
const activeTab = ref('active');

// --- ACCIONES ---

// 1. Mover a papelera (Soft Delete)
const deleteProduct = (id) => {
    if (confirm('¿Mover este producto a la papelera?')) {
        router.delete(route('guinea-pigs.destroy', id), { preserveScroll: true });
    }
};

// 2. Restaurar de la papelera
const restoreProduct = (id) => {
    router.post(route('guinea-pigs.restore', id), {}, { 
        preserveScroll: true,
        onSuccess: () => {
            alert('🐹 ¡Producto restaurado con éxito!');
            activeTab.value = 'active'; 
        }
    });
};

// 3. Eliminar permanentemente (Force Delete)
const forceDeleteProduct = (id) => {
    if (confirm('🔥 ¿ELIMINAR PERMANENTEMENTE? Esta acción no se puede deshacer.')) {
        router.delete(route('guinea-pigs.force-delete', id), { preserveScroll: true });
    }
};

// 4. Toggle Active
const toggleActive = (pig) => {
    pig.active = !pig.active;
    router.patch(`/admin/guinea-pigs/${pig.id}/toggle-active`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            console.log(`Estado de ${pig.name} actualizado`);
        },
        onError: () => {
            pig.active = !pig.active;
            alert('No se pudo actualizar el estado');
        }
    });
};
</script>

<template>
    <Head title="Gestión de Inventario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 p-8 rounded-3xl shadow-2xl border-b-4 border-red-700">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-red-700/10 text-red-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-red-700/20">
                        Inventario
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Gestión de Productos
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Control de existencias y catálogo.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-red-700 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total</span>
                    <p class="text-2xl font-black text-red-400 leading-none">
                        {{ guineaPigs?.length || 0 }} <span class="text-xs text-red-600">productos</span>
                    </p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
                
                <div class="flex gap-4 p-2 bg-slate-200/50 rounded-2xl w-fit">
                    <button @click="activeTab = 'active'" 
                        :class="activeTab === 'active' ? 'bg-white text-red-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                        class="px-6 py-3 rounded-xl font-black uppercase text-xs transition-all flex items-center gap-2">
                        🐾 Activos 
                        <span class="bg-red-100 text-red-600 px-2 py-1 rounded-lg text-[10px]">{{ guineaPigs.length }}</span>
                    </button>
                    <button @click="activeTab = 'trashed'" 
                        :class="activeTab === 'trashed' ? 'bg-white text-rose-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                        class="px-6 py-3 rounded-xl font-black uppercase text-xs transition-all flex items-center gap-2">
                        🗑️ Papelera 
                        <span class="bg-rose-100 text-rose-600 px-2 py-1 rounded-lg text-[10px]">{{ trashedPigs?.length || 0 }}</span>
                    </button>
                </div>

                <div v-if="activeTab === 'active'" class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                            <span class="text-2xl">🐹</span> Productos Activos
                        </h3>
                        <Link :href="route('guinea-pigs.create')" 
                              class="bg-red-700 text-white px-6 py-3 rounded-xl font-black text-sm hover:bg-red-800 transition-all shadow-lg flex items-center gap-2">
                            ➕ NUEVO PRODUCTO
                        </Link>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[800px] text-left border-collapse">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-100">
                                    <th class="p-3 sm:p-4 font-black uppercase text-[10px] text-slate-400 tracking-widest">Producto</th>
                                    <th class="p-3 sm:p-4 font-black uppercase text-[10px] text-slate-400 tracking-widest hidden sm:table-cell">Categoría</th>
                                    <th class="p-3 sm:p-4 font-black uppercase text-[10px] text-slate-400 tracking-widest hidden sm:table-cell">Precio</th>
                                    <th class="p-3 sm:p-4 font-black uppercase text-[10px] text-slate-400 tracking-widest">Stock</th>
                                    <th class="p-3 sm:p-4 font-black uppercase text-[10px] text-slate-400 tracking-widest">Estado</th>
                                    <th class="p-3 sm:p-4 font-black uppercase text-[10px] text-slate-400 tracking-widest text-center">Gestión</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="pig in guineaPigs" :key="pig.id" class="group hover:bg-red-50/30 transition-all">
                                    <td class="p-6">
                                        <div class="flex items-center gap-4">
                                            <div class="relative">
                                                <img v-if="pig.images?.[0]" :src="`/storage/${pig.images[0].image_path}`" class="w-14 h-14 rounded-2xl object-cover shadow-md group-hover:scale-105 transition-transform duration-300 border-2 border-white">
                                                <div v-else class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-2xl border-2 border-dashed border-slate-200">📷</div>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <div class="font-black text-slate-800 text-base">{{ pig.name }}</div>
                                                    <span v-if="pig.active" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-red-100 text-red-700 uppercase">Público</span>
                                                    <span v-else class="px-2 py-0.5 rounded-full text-[10px] font-black bg-gray-100 text-gray-400 uppercase">Oculto</span>
                                                </div>
                                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">
                                                    {{ pig.species }} <span class="mx-1 text-slate-300">•</span> {{ pig.breed || 'Sin raza' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="p-6">
                                        <span :class="{
                                              'bg-red-100 text-red-700 border-red-200': pig.category?.name === 'Animal Vivo',
                                              'bg-red-100 text-red-700 border-red-200': pig.category?.name === 'Carne Beneficiada',
                                              'bg-red-100 text-red-700 border-red-200': pig.category?.name === 'Otros/Insumos'
                                            }" class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase border tracking-wider">
                                            {{ pig.category ? pig.category.name : 'Pendiente' }}
                                        </span>
                                    </td>

                                    <td class="p-6 font-black text-slate-900 text-sm">S/. {{ pig.price }}</td>

                                    <td class="p-6">
                                        <div class="flex flex-col gap-1">
                                            <span :class="pig.stock <= 5 ? 'text-rose-600' : 'text-red-600'" class="text-xs font-black uppercase tracking-tighter">
                                                {{ pig.stock }} unidades
                                            </span>
                                            <div class="w-16 h-1 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full" :class="pig.stock <= 5 ? 'bg-rose-500' : 'bg-red-500'" :style="{ width: Math.min((pig.stock / 20) * 100, 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="p-6">
                                        <button @click="toggleActive(pig)" :class="[ 'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200', pig.active ? 'bg-red-500' : 'bg-gray-200' ]">
                                            <span :class="[ 'inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200', pig.active ? 'translate-x-5' : 'translate-x-0' ]"></span>
                                        </button>
                                    </td>

                                    <td class="p-6">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/product/${pig.id}`" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </Link>
                                            <Link :href="route('guinea-pigs.edit', pig.id)" class="p-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-700 hover:text-white transition-all shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </Link>
                                            <button @click="deleteProduct(pig.id)" class="p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="guineaPigs.length === 0" class="text-center py-24 bg-slate-50/30">
                            <div class="text-6xl mb-4 grayscale opacity-50">🏜️</div>
                            <h3 class="text-slate-800 font-black uppercase text-sm tracking-widest">Lista vacía</h3>
                            <p class="text-slate-400 text-xs font-medium">No se encontraron productos activos.</p>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'trashed'" class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                            <span class="text-2xl">🗑️</span> Papelera de Reciclaje
                        </h3>
                        <p class="text-slate-500 text-xs mt-1">Productos eliminados - pueden restaurarse</p>
                    </div>
                    
                    <div class="p-6">
                        <div v-if="trashedPigs.length > 0" class="space-y-4">
                            <div v-for="pig in trashedPigs" :key="pig.id" class="bg-rose-50 border border-rose-200 rounded-2xl p-6 hover:bg-rose-100 transition-all">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="font-black text-slate-800 line-through">{{ pig.name }}</h4>
                                            <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-700 uppercase">Eliminado</span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm font-bold">
                                            <span class="text-slate-500">ID: <span class="text-slate-700">#{{ pig.id }}</span></span>
                                            <span class="text-slate-500 font-bold">Precio: <span class="line-through text-slate-700 font-black">S/. {{ pig.price }}</span></span>
                                            <span class="text-slate-500">Stock: <span class="text-slate-700">{{ pig.stock }} unidades</span></span>
                                        </div>
                                        <div class="mt-3 text-xs text-rose-600 font-bold">
                                            🗑️ Eliminado el: {{ new Date(pig.deleted_at).toLocaleDateString('es-ES') }}
                                        </div>
                                    </div>
                                    <div class="flex gap-2 w-full md:w-auto">
                                        <button @click="restoreProduct(pig.id)" class="flex-1 md:flex-none bg-red-700 text-white px-4 py-2 rounded-xl text-xs font-black hover:bg-red-800 transition-all shadow-lg">♻️ RESTAURAR</button>
                                        <button @click="forceDeleteProduct(pig.id)" class="flex-1 md:flex-none bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-black hover:bg-red-600 transition-all shadow-lg">🔥 ELIMINAR</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-16">
                            <div class="text-6xl mb-4 grayscale opacity-50">🗑️</div>
                            <h3 class="text-slate-800 font-black uppercase tracking-widest">Papelera vacía</h3>
                            <p class="text-slate-400 text-sm mt-2">No hay productos eliminados actualmente.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>