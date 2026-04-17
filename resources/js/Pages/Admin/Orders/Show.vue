<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    order: Object,
    adminContext: String
});

const getStatusColor = (status) => {
    const colors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'paid': 'bg-blue-100 text-blue-800',
        'shipped': 'bg-orange-100 text-orange-800',
        'delivered': 'bg-green-100 text-green-800',
        'canceled': 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        'pending': 'Pendiente de Pago',
        'paid': 'Pago Confirmado',
        'shipped': 'En Camino',
        'delivered': 'Entregado',
        'canceled': 'Cancelado'
    };
    return texts[status] || status;
};

const getPaymentMethodText = (method) => {
    const texts = {
        'yape': 'Yape 💜',
        'plin': 'Plin 💙',
        'cash': 'Efectivo 💵',
        'transfer': 'Transferencia 🏦'
    };
    return texts[method] || method;
};

const getDeliveryTypeText = (type) => {
    const texts = {
        'recojo': 'Recojo',
        'envio_domicilio': 'Envío a Domicilio',
        'envio_express': 'Envío Express'
    };
    return texts[type] || type;
};

const calculateSubtotal = (order) => {
    return order.items.reduce((total, item) => total + (item.unit_price * item.quantity), 0);
};

const getTotalItems = (order) => {
    return order.items.reduce((total, item) => total + item.quantity, 0);
};
</script>

<template>
    <Head title="Detalle de Pedido - Administración" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 p-8 rounded-3xl shadow-2xl border-b-4 border-emerald-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-emerald-500/20">
                        Ventas
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Pedido #{{ order.id }}
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Detalles completos del pedido.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-emerald-500 pl-4 md:pl-0 md:pr-4">
                    <Link :href="route('admin.orders.index')" 
                          class="inline-flex items-center gap-2 text-emerald-400 hover:text-emerald-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Volver a Lista
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                
                <!-- Información del Pedido -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                            <span class="text-2xl">📋</span> Información del Pedido
                        </h3>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Cliente</p>
                                <p class="font-medium text-slate-800">{{ order.user?.name || 'N/A' }}</p>
                                <p class="text-sm text-slate-500">{{ order.user?.email || 'N/A' }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Fecha</p>
                                <p class="font-medium text-slate-800">{{ new Date(order.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }) }}</p>
                                <p class="text-sm text-slate-500">{{ new Date(order.created_at).toLocaleTimeString('es-ES') }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Estado</p>
                                <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase border tracking-wider inline-block"
                                      :class="getStatusColor(order.status)">
                                    {{ getStatusText(order.status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items del Pedido -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                            <span class="text-2xl">🛒</span> Items del Pedido
                        </h3>
                    </div>
                    <div class="p-8">
                        <div v-if="order.items && order.items.length > 0" class="space-y-4">
                            <div v-for="item in order.items" :key="item.id" 
                                 class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl">
                                <div v-if="item.guineaPig?.images?.length > 0" class="w-16 h-16 rounded-lg overflow-hidden bg-white flex-shrink-0">
                                    <img :src="item.guineaPig.images[0].image_path" 
                                         class="w-full h-full object-cover"
                                         @error="(e) => e.target.style.display = 'none'" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-slate-800">{{ item.guineaPig?.name || 'Producto' }}</p>
                                    <p class="text-sm text-slate-500">Cantidad: {{ item.quantity }}</p>
                                </div>
                                <p class="font-medium text-slate-800">S/. {{ (item.price * item.quantity).toFixed(2) }}</p>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-slate-400">
                            No hay items en este pedido
                        </div>
                    </div>
                </div>

                <!-- Detalles de Envío y Pago -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                                <span class="text-2xl">📍</span> Información de Envío
                            </h3>
                        </div>
                        <div class="p-8 space-y-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Tipo de Entrega</p>
                                <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase border tracking-wider inline-block bg-blue-50 text-blue-700 border-blue-200">
                                    {{ getDeliveryTypeText(order.delivery_type || 'recojo') }}
                                </span>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Dirección de Entrega</p>
                                <p class="text-slate-800">{{ order.shipping_address || 'No especificada' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                                <span class="text-2xl">💳</span> Información de Pago
                            </h3>
                        </div>
                        <div class="p-8 space-y-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Método de Pago</p>
                                <p class="text-slate-800">{{ getPaymentMethodText(order.payment_method) }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Subtotal (productos):</span>
                                    <span class="font-medium text-slate-800">S/. {{ calculateSubtotal(order).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Envío:</span>
                                    <span class="font-medium text-slate-800">S/. {{ parseFloat(order.shipping_cost || 0).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-slate-200">
                                    <span class="font-black text-slate-800">Total:</span>
                                    <span class="font-black text-emerald-600 text-lg">S/. {{ parseFloat(order.total || 0).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
