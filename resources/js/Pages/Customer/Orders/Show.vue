<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    order: Object,
    userContext: String,
    whatsappNumber: String,
    timestamp: Number,
    trackings: Array
});

const whatsappLink = computed(() => {
    const phone = props.whatsappNumber || "51987658914";
    const message = encodeURIComponent(
        `¡Hola Mundo Yacus! 🐹\n\n` +
        `Deseo confirmar el pago de mi pedido:\n` +
        `📦 *Pedido:* #${props.order.id}\n` +
        `💰 *Total:* S/. ${props.order.total}\n\n` +
        `Aquí adjunto mi comprobante de pago. 👇` 
    );
    return `https://wa.me/${phone}?text=${message}`;
});

const formatStatus = (status) => {
    const statusMap = {
        'pending': 'Pendiente de Pago',
        'paid': 'Pago Confirmado',
        'shipped': 'En Camino a Destino',
        'delivered': 'Entregado en Mano',
        'canceled': 'Trato Cancelado'
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

const getStatusIcon = (status) => {
    const iconMap = {
        'pending': '⏳',
        'paid': '✅',
        'shipped': '🚚',
        'delivered': '📦',
        'canceled': '❌'
    };
    return iconMap[status] || '📋';
};
</script>

<template>
    <Head :title="`Pedido #${order.id} - Mundo Yacus`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 px-2">
                <!-- Left: order info -->
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 tracking-wide mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 inline-block"></span>
                        Detalle de Intercambio
                    </span>
                    <h2 class="text-2xl font-medium text-gray-900 leading-tight">Pedido #{{ order.id }}</h2>
                    <p class="text-sm text-gray-400 mt-0.5">
                        Realizado: {{ new Date(order.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                    </p>
                </div>

                <!-- Right: status and total -->
                <div class="text-right space-y-2">
                    <div>
                        <span :class="['px-3 py-1 text-[10px] font-medium uppercase border rounded-lg', getStatusColor(order.status)]">
                            {{ formatStatus(order.status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Total Inversión</p>
                        <p class="text-xl font-medium text-gray-900">S/. {{ parseFloat(order.total).toFixed(2) }}</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

                <!-- WhatsApp para pagos pendientes -->
                <div v-if="order.status === 'pending'">
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-amber-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 21l1.65-5.34A7.92 7.92 0 0 1 3 12a8 8 0 1 1 8 8 7.92 7.92 0 0 1-3.66-.89L3 21z"/>
                                    <path d="M9 10h.01M15 10h.01"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">¿Listo para confirmar tu pago?</h3>
                                <p class="text-gray-600 text-sm mb-4">
                                    Envía tu comprobante por WhatsApp y activaremos tu pedido inmediatamente.
                                </p>
                                <a :href="whatsappLink" target="_blank"
                                   class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 21l1.65-5.34A7.92 7.92 0 0 1 3 12a8 8 0 1 1 8 8 7.92 7.92 0 0 1-3.66-.89L3 21z"/>
                                        <path d="M9 10h.01M15 10h.01"/>
                                    </svg>
                                    Enviar Comprobante Ahora
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmación de pago -->
                <div v-else-if="order.status === 'paid'">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">¡Pago Verificado</h3>
                                <p class="text-gray-600 text-sm">Estamos preparando tu pedido para envío.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del cliente -->
                <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-100 p-4">
                        <h2 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Datos del Cliente
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Nombre Completo</p>
                                    <p class="font-black text-stone-900">{{ order.user?.name || 'No especificado' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-stone-400 tracking-widest mb-1">Email</p>
                                    <p class="font-black text-stone-900">{{ order.user?.email || 'No especificado' }}</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-stone-400 tracking-widest mb-1">Teléfono</p>
                                    <p class="font-black text-stone-900">{{ order.phone || 'No especificado' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-stone-400 tracking-widest mb-1">Dirección de Entrega</p>
                                    <p class="font-black text-stone-900">{{ order.address || 'No especificado' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos del pedido -->
                <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-100 p-4">
                        <h2 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            Productos del Intercambio
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="item in order.items" :key="item.id" class="p-4">
                            <div class="flex flex-wrap gap-4">
                                <!-- Imagen -->
                                <div class="flex-shrink-0">
                                    <Link :href="`/product/${item.guinea_pig_id}`" class="block group">
                                        <div class="w-20 h-20 bg-gray-50 rounded-lg border border-gray-200 overflow-hidden group-hover:border-amber-500 transition-colors">
                                            <!-- Imagen real del producto -->
                                            <img v-if="item.guineaPig?.images?.[0]?.image_path"
                                                 :src="`/storage/${item.guineaPig.images[0].image_path}`"
                                                 :alt="item.guineaPig?.name || 'Producto'"
                                                 class="w-full h-full object-cover"
                                                 @error="$event.target.style.display='none'; $event.target.nextElementSibling.style.display='flex'">
                                            <!-- Fallback con icono si no hay imagen o hay error -->
                                            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                                <div class="text-center">
                                                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                                        <path d="M21 15l-5-5L5 21"/>
                                                    </svg>
                                                    <span class="text-[8px] text-gray-500 block">Sin foto</span>
                                                </div>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                                <!-- Detalles -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-medium text-gray-900 mb-2">
                                        <Link :href="`/product/${item.guinea_pig_id}`" 
                                              class="text-gray-900 hover:text-amber-600 transition-colors">
                                            {{ item.guineaPig?.name || 'Producto' }}
                                        </Link>
                                    </h3>
                                    <div class="space-y-1 text-sm">
                                        <p class="text-gray-600 flex items-center gap-1">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                                <circle cx="12" cy="9" r="2.5"/>
                                            </svg>
                                            {{ item.guineaPig?.seller?.name || 'Mundo Yacus' }}
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-medium text-gray-900">Precio unitario:</span> 
                                            S/. {{ parseFloat(item.unit_price || item.price).toFixed(2) }}
                                        </p>
                                    </div>
                                    
                                    <!-- Motivación para pago pendiente -->
                                    <div v-if="order.status === 'pending'" class="mt-3 p-2 bg-amber-50 border border-amber-200 rounded-lg">
                                        <p class="text-amber-700 text-xs font-medium">
                                            Activa tu pago para desbloquear beneficios exclusivos
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Cantidades y totales -->
                                <div class="text-right min-w-0">
                                    <div class="mb-3">
                                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Cantidad</p>
                                        <p class="text-lg font-medium text-gray-900">{{ item.quantity }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Subtotal</p>
                                        <p class="text-lg font-medium text-amber-700">
                                            S/. {{ (parseFloat(item.unit_price || item.price) * item.quantity).toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline de Seguimiento -->
                <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-100 p-4">
                        <h2 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                <circle cx="12" cy="9" r="2.5"/>
                            </svg>
                            Seguimiento del Pedido
                        </h2>
                    </div>
                    <div class="p-4">
                        <div v-if="trackings && trackings.length > 0" class="space-y-4">
                            <div v-for="(tracking, index) in trackings" :key="tracking.id" 
                                 class="flex gap-3 items-start">
                                <!-- Timeline line -->
                                <div v-if="index < trackings.length - 1" 
                                     class="w-0.5 h-12 bg-gray-300 mt-6"></div>
                                
                                <!-- Status circle -->
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                                     :class="getStatusColor(tracking.status)">
                                    {{ getStatusIcon(tracking.status) }}
                                </div>
                                
                                <!-- Status info -->
                                <div class="flex-1 min-w-0 pb-3">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ formatStatus(tracking.status) }}</p>
                                            <p v-if="tracking.notes" class="text-gray-600 text-sm mt-1">{{ tracking.notes }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">
                                                {{ new Date(tracking.status_updated_at).toLocaleDateString('es-ES', { 
                                                    day: '2-digit', 
                                                    month: 'short', 
                                                    hour: '2-digit', 
                                                    minute: '2-digit' 
                                                }) }}
                                            </p>
                                        </div>
                                    </div>
                                    <p v-if="tracking.updatedBy" class="text-gray-500 text-xs mt-2">
                                        Actualizado por: {{ tracking.updatedBy?.name || 'Sistema' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <span class="text-gray-400">No hay historial de seguimiento disponible</span>
                        </div>
                    </div>
                </div>

                <!-- Resumen del pedido -->
                <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-100 p-4">
                        <h2 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            Resumen Financiero
                        </h2>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium uppercase tracking-wide text-[10px]">Subtotal</span>
                            <span class="font-medium text-gray-900">S/. {{ parseFloat(order.total).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium uppercase tracking-wide text-[10px]">Envío</span>
                            <span class="font-medium text-emerald-600">A convenir por WhatsApp</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-base font-medium text-gray-900 uppercase tracking-wide">Total</span>
                            <span class="text-xl font-medium text-amber-700">
                                S/. {{ parseFloat(order.total).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
