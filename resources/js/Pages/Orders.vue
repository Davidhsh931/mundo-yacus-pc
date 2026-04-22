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
        'pending': 'bg-red-100 text-red-800 border-red-200',
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
                <!-- Left: identity -->
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 tracking-wide mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600 inline-block"></span>
                        Mis Pedidos · Historial
                    </span>
                    <h2 class="text-2xl font-medium text-gray-900 leading-tight">Mis Ordenes</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Revisa el estado y detalles de tus compras.</p>
                </div>

                <!-- Right: orders count -->
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 text-right shrink-0">
                    <p class="text-[11px] font-medium text-red-700 tracking-wide">Total Pedidos</p>
                    <p class="text-xl font-medium text-red-900 leading-tight">
                        {{ orders.length.toLocaleString() }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">

                <!-- Section heading -->
                <div class="flex justify-between items-baseline px-4 sm:px-0">
                    <span class="text-sm font-medium text-gray-700">Pedidos realizados</span>
                    <span class="text-xs text-gray-400">{{ orders.length }} pedido{{ orders.length !== 1 ? 's' : '' }}</span>
                </div>

                <!-- Orders grid -->
                <div v-if="orders && orders.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="order in orders" :key="order.id" 
                         class="group bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:border-gray-200 hover:-translate-y-0.5 hover:shadow-md transition-all duration-200">
                        
                        <!-- Order header -->
                        <div class="relative bg-gradient-to-r from-red-50 to-orange-50 p-6 border-b border-gray-100">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl">**</span>
                                        <span class="text-xs font-medium text-red-700 uppercase tracking-wide">Orden #{{ order.id }}</span>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                                        {{ formatStatus(order.status) }}
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        {{ new Date(order.created_at).toLocaleDateString('es-PE', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span :class="['px-3 py-1.5 text-xs font-medium uppercase border rounded-full', getStatusColor(order.status)]">
                                        {{ formatStatus(order.status) }}
                                    </span>
                                    <p class="text-xl font-bold text-gray-900 mt-2">S/. {{ parseFloat(order.total).toFixed(2) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order items -->
                        <div class="p-6">
                            <div class="space-y-4">
                                <div v-for="item in order.items" :key="item.id" 
                                     class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl hover:bg-red-50 transition-colors">
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                                        <img v-if="item.guinea_pig?.images?.[0]" 
                                             :src="`/storage/${item.guinea_pig.images[0].image_path}`" 
                                             class="w-full h-full object-cover">
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <path d="M21 15l-5-5L5 21"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">
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
                                        <p class="text-sm font-bold text-gray-900">S/. {{ (item.price * item.quantity).toFixed(2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order actions -->
                        <div class="bg-gray-50 p-4 flex justify-between items-center border-t border-gray-100">
                            <div class="text-xs text-gray-500">
                                Total: <span class="font-bold text-gray-900">S/. {{ parseFloat(order.total).toFixed(2) }}</span>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="`/orders/${order.id}`" 
                                      class="px-4 py-2 bg-red-600 text-white text-xs font-medium hover:bg-red-700 transition-colors rounded-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Ver Detalles
                                </Link>
                                <button v-if="!isAdmin && order.status === 'pending'"
                                        @click="cancelOrder(order.id)"
                                        class="px-4 py-2 border border-red-200 text-red-600 text-xs font-medium hover:bg-red-50 transition-colors rounded-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                    Anular
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="bg-white border border-gray-100 rounded-2xl py-16 text-center">
                    <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-red-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <p class="text-gray-400 font-medium uppercase tracking-wide mb-4 text-sm">No hay pedidos registrados</p>
                    <p class="text-gray-500 text-sm mb-6">Tu historial de compras aparecerá aquí</p>
                    <Link href="/" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition-colors rounded-lg">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        Explorar Productos
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>