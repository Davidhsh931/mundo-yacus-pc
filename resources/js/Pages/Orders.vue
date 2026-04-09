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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 px-2">
                <!-- Left: orders info -->
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 tracking-wide mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 inline-block"></span>
                        Mis Pedidos
                    </span>
                    <h2 class="text-2xl font-medium text-gray-900 leading-tight">Historial de Pedidos</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Revisa el estado de tus compras.</p>
                </div>

                <!-- Right: orders count -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 text-right shrink-0">
                    <p class="text-[11px] font-medium text-amber-700 tracking-wide">Total Pedidos</p>
                    <p class="text-xl font-medium text-amber-900 leading-tight">
                        {{ orders.length.toLocaleString() }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">

                <div v-if="orders && orders.length > 0" class="space-y-4">
                    <div v-for="order in orders" :key="order.id" 
                         class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
                        
                        <div class="bg-gray-50 p-4 border-b border-gray-100 flex flex-wrap justify-between items-center gap-4">
                            <div>
                                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Nº Registro</p>
                                <h3 class="text-lg font-medium text-gray-900">#{{ order.id }}</h3>
                                <p class="text-xs text-gray-500">{{ new Date(order.created_at).toLocaleDateString() }}</p>
                            </div>
                            <div class="text-right">
                                <span :class="['px-2 py-1 text-[10px] font-medium uppercase border rounded-lg', getStatusColor(order.status)]">
                                    {{ formatStatus(order.status) }}
                                </span>
                                <p class="text-lg font-medium text-gray-900 mt-1">S/. {{ parseFloat(order.total).toFixed(2) }}</p>
                            </div>
                        </div>

                        <div class="p-4 divide-y divide-gray-100">
                            <div v-for="item in order.items" :key="item.id" class="py-3 flex items-center gap-3 first:pt-0 last:pb-0">
                                <div class="w-12 h-12 bg-gray-50 rounded-lg flex-shrink-0 overflow-hidden">
                                    <img v-if="item.guinea_pig?.images?.[0]" 
                                         :src="`/storage/${item.guinea_pig.images[0].image_path}`" 
                                         class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-sm">
                                        <svg class="w-6 h-6 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <path d="M21 15l-5-5L5 21"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">
                                        {{ item.guinea_pig?.name || 'Producto' }}
                                    </h4>
                                    <p class="text-xs text-gray-500 flex items-center gap-1">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                            <circle cx="12" cy="9" r="2.5"/>
                                        </svg>
                                        {{ item.guinea_pig?.seller?.name || 'Yacus' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Cantidad</p>
                                    <p class="text-sm font-medium text-gray-900">{{ item.quantity }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Subtotal</p>
                                    <p class="text-sm font-medium text-gray-900">S/. {{ (item.price * item.quantity).toFixed(2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-3 flex justify-end gap-2 border-t border-gray-100">
                            <Link :href="`/orders/${order.id}`" 
                                  class="px-3 py-1.5 bg-amber-600 text-white text-xs font-medium hover:bg-amber-700 transition-colors rounded-lg">
                                Ver Detalles
                            </Link>
                            <button v-if="!isAdmin && order.status === 'pending'"
                                    @click="cancelOrder(order.id)"
                                    class="px-3 py-1.5 border border-red-200 text-red-600 text-xs font-medium hover:bg-red-50 transition-colors rounded-lg">
                                Anular
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border border-gray-100 rounded-2xl py-16 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <p class="text-gray-400 font-medium uppercase tracking-wide mb-4 text-sm">No hay registros de pedidos</p>
                    <Link href="/" class="inline-block bg-amber-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:bg-amber-700 transition-colors">
                        Ir a la tienda
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>