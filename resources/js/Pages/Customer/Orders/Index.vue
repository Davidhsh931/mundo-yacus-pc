<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    orders: Array,
    userContext: String,
    isAdmin: Boolean,
    success: String
});

const formatStatus = (status) => {
    const statusMap = {
        'pending': 'Pendiente de Pago',
        'paid': 'Pago Confirmado',
        'shipped': 'En Camino',
        'delivered': 'Entregado',
        'canceled': 'Cancelado'
    };
    return statusMap[status] || status;
};

const getStatusColor = (status) => {
    const colorMap = {
        'pending': 'bg-amber-100 text-amber-800 border-amber-200',
        'paid': 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'shipped': 'bg-stone-800 text-stone-100 border-stone-700',
        'delivered': 'bg-stone-200 text-stone-800 border-stone-300',
        'canceled': 'bg-red-100 text-red-800 border-red-200'
    };
    return colorMap[status] || 'bg-gray-100 text-gray-800';
};

const cancelOrder = (orderId) => {
    if (confirm('¿Deseas anular este registro de pedido?')) {
        router.patch(`/orders/${orderId}/cancel`, {}, {
            onSuccess: () => {},
            onError: (errors) => alert('Error: ' + (errors.message || 'Intente nuevamente'))
        });
    }
};
</script>

<template>
    <Head title="Mis Pedidos - Mundo Yacus" />
    <AuthenticatedLayout>
        <div class="py-12 bg-stone-100 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-12">
                    <div class="bg-stone-900 p-8 text-white border-b-8 border-amber-600 shadow-xl relative overflow-hidden rounded-t-lg">
                        <div class="relative z-10">
                            <span class="text-[10px] font-black uppercase tracking-widest text-amber-500 block mb-1">Archivo de Intercambios</span>
                            <h2 class="text-3xl font-black italic uppercase tracking-tighter text-white">
                                Mis <span class="text-amber-500">Pedidos</span>
                            </h2>
                        </div>
                    </div>
                </div>

                <div v-if="orders && orders.length > 0" class="space-y-8">
                    <div v-for="order in orders" :key="order.id" 
                         class="bg-white border border-stone-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        
                        <div class="bg-stone-50 p-6 border-b border-stone-100 flex flex-wrap justify-between items-center gap-4">
                            <div>
                                <p class="text-[10px] font-bold text-stone-400 uppercase">Nº Registro</p>
                                <h3 class="text-lg font-black text-stone-900">#{{ order.id }}</h3>
                                <p class="text-xs text-stone-500">{{ new Date(order.created_at).toLocaleDateString() }}</p>
                            </div>
                            <div class="text-right">
                                <span :class="['px-3 py-1 text-[10px] font-black uppercase border rounded-full', getStatusColor(order.status)]">
                                    {{ formatStatus(order.status) }}
                                </span>
                                <p class="text-xl font-black text-stone-900 mt-1">S/. {{ (parseFloat(order.total) || 0).toFixed(2) }}</p>
                            </div>
                        </div>

                        <div class="p-6 divide-y divide-stone-100">
                            <div v-for="item in order.items" :key="item.id" class="py-4 flex items-center gap-4 first:pt-0 last:pb-0">
                                <div class="w-16 h-16 bg-stone-100 rounded border border-stone-200 flex-shrink-0 overflow-hidden">
                                    <img v-if="item.guinea_pig?.images?.[0]" 
                                         :src="`/storage/${item.guinea_pig.images[0].image_path}`" 
                                         class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-xl">🐹</div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-stone-900 uppercase tracking-tight">
                                        {{ item.guinea_pig?.name || 'Producto' }}
                                    </h4>
                                    <p class="text-[10px] text-stone-500 uppercase">Vendedor: {{ item.guinea_pig?.seller?.name || 'Yacus' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-stone-500">Cant: {{ item.quantity }}</p>
                                    <p class="text-sm font-bold text-stone-900">S/. {{ (parseFloat(item.price || item.guinea_pig?.price || 0) * item.quantity).toFixed(2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-stone-50 p-4 flex justify-end gap-3 border-t border-stone-100">
                            <Link :href="`/orders/${order.id}`" 
                                  class="px-4 py-2 bg-stone-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-amber-600 transition-colors rounded">
                                Ver Detalles
                            </Link>
                            <button v-if="!isAdmin && order.status === 'pending'"
                                    @click="cancelOrder(order.id)"
                                    class="px-4 py-2 border border-red-200 text-red-600 text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition-colors rounded">
                                Anular
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border-2 border-dashed border-stone-200 rounded-xl py-20 text-center">
                    <p class="text-stone-400 font-bold uppercase tracking-widest">No hay registros de pedidos</p>
                    <Link href="/" class="mt-4 inline-block bg-amber-600 text-white px-8 py-3 rounded font-black uppercase text-xs">Ir a la tienda</Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
