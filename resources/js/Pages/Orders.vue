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
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-950 p-8 rounded-3xl shadow-2xl border-b-8 border-yellow-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-yellow-500/20">
                        Historial
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Mis Pedidos
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Registro de tus intercambios en la chacra.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-yellow-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total Pedidos</span>
                    <p class="text-3xl font-black text-yellow-400 leading-none">{{ orders?.length || 0 }} <span class="text-xs text-yellow-600">órdenes</span></p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">

                <div v-if="orders && orders.length > 0" class="space-y-8">
                    <div v-for="order in orders" :key="order.id" 
                         class="group bg-white rounded-[2rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100">
                        
                        <div class="bg-gray-50 p-6 border-b border-gray-100 flex flex-wrap justify-between items-center gap-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nº Registro</p>
                                <h3 class="text-lg font-black text-gray-900 tracking-tighter">#{{ order.id }}</h3>
                                <p class="text-xs text-gray-500">{{ new Date(order.created_at).toLocaleDateString() }}</p>
                            </div>
                            <div class="text-right">
                                <span :class="['px-3 py-1 text-[10px] font-black uppercase border rounded-full', getStatusColor(order.status)]">
                                    {{ formatStatus(order.status) }}
                                </span>
                                <p class="text-xl font-black text-gray-900 mt-1">S/. {{ parseFloat(order.total).toFixed(2) }}</p>
                            </div>
                        </div>

                        <div class="p-6 divide-y divide-gray-100">
                            <div v-for="item in order.items" :key="item.id" class="py-4 flex items-center gap-4 first:pt-0 last:pb-0">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex-shrink-0 overflow-hidden">
                                    <img v-if="item.guinea_pig?.images?.[0]" 
                                         :src="`/storage/${item.guinea_pig.images[0].image_path}`" 
                                         class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-xl">🐹</div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight">
                                        {{ item.guinea_pig?.name || 'Producto' }}
                                    </h4>
                                    <p class="text-[10px] text-gray-500 uppercase">📍 {{ item.guinea_pig?.seller?.name || 'Yacus' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 uppercase tracking-widest">Cantidad</p>
                                    <p class="text-lg font-black text-gray-900">{{ item.quantity }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 uppercase tracking-widest">Subtotal</p>
                                    <p class="text-lg font-black text-gray-900">S/. {{ (item.price * item.quantity).toFixed(2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 flex justify-end gap-3 border-t border-gray-100">
                            <Link :href="`/orders/${order.id}`" 
                                  class="px-4 py-2 bg-gray-950 text-white text-[10px] font-black uppercase tracking-widest hover:bg-yellow-500 transition-colors rounded-xl">
                                Ver Detalles
                            </Link>
                            <button v-if="!isAdmin && order.status === 'pending'"
                                    @click="cancelOrder(order.id)"
                                    class="px-4 py-2 border border-red-200 text-red-600 text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition-colors rounded-xl">
                                Anular
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white rounded-[2rem] border-2 border-dashed border-gray-200 py-20 text-center">
                    <div class="text-6xl mb-4">📦</div>
                    <p class="text-gray-400 font-bold uppercase tracking-widest mb-4">No hay registros de pedidos</p>
                    <Link href="/" class="inline-block bg-yellow-500 text-gray-950 px-8 py-3 rounded-xl font-black uppercase text-xs hover:bg-gray-950 hover:text-yellow-400 transition-all">
                        Ir a la tienda
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>