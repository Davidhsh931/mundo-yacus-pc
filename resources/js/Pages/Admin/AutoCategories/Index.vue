<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const suggestedCategories = ref([]);
const loading = ref(false);
const generating = ref(false);

const fetchSuggestedCategories = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/auto-categories');
        const data = await response.json();
        suggestedCategories.value = data;
    } catch (error) {
        console.error('Error fetching suggested categories:', error);
    } finally {
        loading.value = false;
    }
};

const generateGroups = async () => {
    if (!confirm('¿Generar nuevos grupos de categorías con IA? Esto borrará las sugerencias anteriores.')) {
        return;
    }

    generating.value = true;
    try {
        const response = await fetch('/admin/auto-categories/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        
        if (data.success) {
            alert(`✅ Se generaron ${data.groups_count} grupos de categorías`);
            await fetchSuggestedCategories();
        } else {
            alert('❌ Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error generating groups:', error);
        alert('❌ Error al generar grupos');
    } finally {
        generating.value = false;
    }
};

const approveGroup = async (id) => {
    if (!confirm('¿Aprobar este grupo? Se creará una nueva categoría y se asignarán los productos.')) {
        return;
    }

    try {
        const response = await fetch(`/admin/auto-categories/${id}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Grupo aprobado y categoría creada');
            await fetchSuggestedCategories();
        } else {
            alert('❌ Error al aprobar grupo');
        }
    } catch (error) {
        console.error('Error approving group:', error);
        alert('❌ Error al aprobar grupo');
    }
};

const rejectGroup = async (id) => {
    if (!confirm('¿Rechazar este grupo?')) {
        return;
    }

    try {
        const response = await fetch(`/admin/auto-categories/${id}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Grupo rechazado');
            await fetchSuggestedCategories();
        } else {
            alert('❌ Error al rechazar grupo');
        }
    } catch (error) {
        console.error('Error rejecting group:', error);
        alert('❌ Error al rechazar grupo');
    }
};

onMounted(() => {
    fetchSuggestedCategories();
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Agrupamiento Automático de Categorías" />

        <div class="py-12 bg-slate-100/50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Agrupamiento Automático</h1>
                            <p class="text-gray-500 mt-1">La IA agrupa productos automáticamente. Tú validas los grupos.</p>
                        </div>
                        <button
                            @click="generateGroups"
                            :disabled="generating"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-bold transition-all disabled:opacity-50 flex items-center gap-2"
                        >
                            <span v-if="generating">🔄 Generando...</span>
                            <span v-else>🤖 Generar Grupos</span>
                        </button>
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-red-600 border-t-transparent"></div>
                    <p class="text-gray-500 mt-4">Cargando sugerencias...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="suggestedCategories.length === 0" class="bg-white rounded-2xl shadow-sm p-12 text-center">
                    <div class="text-6xl mb-4">🤖</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Sin sugerencias pendientes</h3>
                    <p class="text-gray-500 mb-6">Haz clic en "Generar Grupos" para que la IA analice tus productos y sugiera categorías.</p>
                </div>

                <!-- Suggested Categories List -->
                <div v-else class="space-y-4">
                    <div
                        v-for="group in suggestedCategories"
                        :key="group.id"
                        class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:border-red-200 transition-all"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900">{{ group.name }}</h3>
                                <p v-if="group.description" class="text-gray-500 mt-1">{{ group.description }}</p>
                                <p class="text-sm text-gray-400 mt-2">{{ group.product_ids?.length || 0 }} productos en este grupo</p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="approveGroup(group.id)"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-2"
                                >
                                    ✓ Aprobar
                                </button>
                                <button
                                    @click="rejectGroup(group.id)"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-all"
                                >
                                    ✕ Rechazar
                                </button>
                            </div>
                        </div>

                        <!-- Products Preview -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-bold text-gray-500 uppercase mb-3">Productos en este grupo:</p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="productId in group.product_ids"
                                    :key="productId"
                                    class="bg-white border border-gray-200 px-3 py-1 rounded-lg text-sm text-gray-700"
                                >
                                    #{{ productId }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
