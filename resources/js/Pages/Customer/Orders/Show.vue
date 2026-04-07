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
        <div class="py-12 bg-stone-100 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Header stone-900 con ámbar -->
                <div class="mb-12">
                    <div class="bg-stone-900 p-8 text-white border-b-8 border-amber-600 shadow-xl relative overflow-hidden rounded-t-lg">
                        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');"></div>
                        
                        <div class="relative z-10">
                            <div class="flex flex-wrap justify-between items-start gap-6">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-amber-500 block mb-2">Detalle de Intercambio</span>
                                    <h1 class="text-3xl sm:text-4xl font-black italic uppercase tracking-tighter text-white mb-3">
                                        Pedido #{{ order.id }}
                                    </h1>
                                    <p class="text-stone-300 text-sm">
                                        <span class="text-amber-500 font-bold">●</span> 
                                        Realizado: {{ new Date(order.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                                    </p>
                                </div>
                                
                                <div class="text-right">
                                    <div class="inline-block">
                                        <span :class="['px-4 py-2 text-[10px] font-black uppercase border-2 rounded-full', getStatusColor(order.status)]">
                                            {{ formatStatus(order.status) }}
                                        </span>
                                    </div>
                                    <div class="mt-4">
                                        <p class="text-[10px] font-bold uppercase text-stone-400 tracking-widest">Total Inversión</p>
                                        <p class="text-3xl font-black text-white">S/. {{ parseFloat(order.total).toFixed(2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp para pagos pendientes -->
                <div v-if="order.status === 'pending'" class="mb-8">
                    <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xl">📱</span>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-black text-stone-900 mb-2">¿Listo para confirmar tu pago?</h3>
                                <p class="text-stone-600 text-sm mb-4">
                                    Envía tu comprobante por WhatsApp y activaremos tu pedido inmediatamente.
                                </p>
                                <a :href="whatsappLink" target="_blank"
                                   class="inline-flex items-center gap-2 bg-[#25D366] hover:bg-[#128C7E] text-white font-black py-3 px-6 rounded-xl transition-all hover:scale-[1.02] shadow-lg">
                                    <span class="text-xl">💬</span>
                                    ENVIAR COMPROBANTE AHORA
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmación de pago -->
                <div v-else-if="order.status === 'paid'" class="mb-8">
                    <div class="bg-emerald-50 border-2 border-emerald-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-xl">✅</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-stone-900">¡Pago Verificado</h3>
                                <p class="text-stone-600 text-sm">Estamos preparando tu pedido para envío.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del cliente -->
                <div class="bg-white border border-stone-200 rounded-xl overflow-hidden shadow-sm mb-8">
                    <div class="bg-stone-50 border-b border-stone-200 p-6">
                        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
                            <span class="text-amber-500">👤</span>
                            Datos del Cliente
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-stone-400 tracking-widest mb-1">Nombre Completo</p>
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
                <div class="bg-white border border-stone-200 rounded-xl overflow-hidden shadow-sm mb-8">
                    <div class="bg-stone-50 border-b border-stone-200 p-6">
                        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
                            <span class="text-amber-500">🛒</span>
                            Productos del Intercambio
                        </h2>
                    </div>
                    <div class="divide-y divide-stone-100">
                        <div v-for="item in order.items" :key="item.id" class="p-6">
                            <div class="flex flex-wrap gap-6">
                                <!-- Imagen -->
                                <div class="flex-shrink-0">
                                    <Link :href="`/product/${item.guinea_pig_id}`" class="block group">
                                        <div class="w-24 h-24 bg-stone-100 rounded-lg border-2 border-stone-200 overflow-hidden group-hover:border-amber-500 transition-colors">
                                            <!-- Imagen real del producto -->
                                            <img v-if="item.guineaPig?.images?.[0]?.image_path"
                                                 :src="`/storage/${item.guineaPig.images[0].image_path}`"
                                                 :alt="item.guineaPig?.name || 'Producto'"
                                                 class="w-full h-full object-cover"
                                                 @error="$event.target.style.display='none'; $event.target.nextElementSibling.style.display='flex'">
                                            <!-- Fallback con emoji si no hay imagen o hay error -->
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-amber-50 to-stone-100">
                                                <div class="text-center">
                                                    <span class="text-3xl block mb-1">🐹</span>
                                                    <span class="text-[8px] text-stone-500 block">Sin foto</span>
                                                </div>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                                
                                <!-- Detalles -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-black text-stone-900 mb-2">
                                        <Link :href="`/product/${item.guinea_pig_id}`" 
                                              class="text-stone-900 hover:text-amber-600 transition-colors">
                                            {{ item.guineaPig?.name || 'Producto' }}
                                        </Link>
                                    </h3>
                                    <div class="space-y-2 text-sm">
                                        <p class="text-stone-600">
                                            <span class="font-bold text-stone-900">Vendedor:</span> 
                                            {{ item.guineaPig?.seller?.name || 'Mundo Yacus' }}
                                        </p>
                                        <p class="text-stone-600">
                                            <span class="font-bold text-stone-900">Precio unitario:</span> 
                                            S/. {{ parseFloat(item.unit_price || item.price).toFixed(2) }}
                                        </p>
                                    </div>
                                    
                                    <!-- Motivación para pago pendiente -->
                                    <div v-if="order.status === 'pending'" class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                        <p class="text-amber-700 text-xs font-bold">
                                            🔒 Activa tu pago para desbloquear beneficios exclusivos
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Cantidades y totales -->
                                <div class="text-right min-w-0">
                                    <div class="mb-4">
                                        <p class="text-[10px] font-bold uppercase text-stone-400 tracking-widest mb-1">Cantidad</p>
                                        <p class="text-2xl font-black text-stone-900">{{ item.quantity }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase text-stone-400 tracking-widest mb-1">Subtotal</p>
                                        <p class="text-2xl font-black text-amber-600">
                                            S/. {{ (parseFloat(item.unit_price || item.price) * item.quantity).toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline de Seguimiento -->
                <div class="bg-white border border-stone-200 rounded-xl overflow-hidden shadow-sm mb-8">
                    <div class="bg-stone-50 border-b border-stone-200 p-6">
                        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
                            <span class="text-amber-500">📍</span>
                            Seguimiento del Pedido
                        </h2>
                    </div>
                    <div class="p-6">
                        <div v-if="trackings && trackings.length > 0" class="space-y-4">
                            <div v-for="(tracking, index) in trackings" :key="tracking.id" 
                                 class="flex gap-4 items-start">
                                <!-- Timeline line -->
                                <div v-if="index < trackings.length - 1" 
                                     class="w-0.5 h-16 bg-stone-300 mt-8"></div>
                                
                                <!-- Status circle -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-lg font-black"
                                     :class="getStatusColor(tracking.status)">
                                    {{ getStatusIcon(tracking.status) }}
                                </div>
                                
                                <!-- Status info -->
                                <div class="flex-1 min-w-0 pb-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-black text-stone-900">{{ formatStatus(tracking.status) }}</p>
                                            <p v-if="tracking.notes" class="text-stone-600 text-sm mt-1">{{ tracking.notes }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-bold uppercase text-stone-400 tracking-widest">
                                                {{ new Date(tracking.status_updated_at).toLocaleDateString('es-ES', { 
                                                    day: '2-digit', 
                                                    month: 'short', 
                                                    hour: '2-digit', 
                                                    minute: '2-digit' 
                                                }) }}
                                            </p>
                                        </div>
                                    </div>
                                    <p v-if="tracking.updatedBy" class="text-stone-500 text-xs mt-2">
                                        Actualizado por: {{ tracking.updatedBy?.name || 'Sistema' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <span class="text-stone-400">No hay historial de seguimiento disponible</span>
                        </div>
                    </div>
                </div>

                <!-- Resumen del pedido -->
                <div class="bg-white border border-stone-200 rounded-xl overflow-hidden shadow-sm mb-8">
                    <div class="bg-stone-50 border-b border-stone-200 p-6">
                        <h2 class="text-lg font-black text-stone-900 flex items-center gap-2">
                            <span class="text-amber-500">💰</span>
                            Resumen Financiero
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center pb-4 border-b border-stone-200">
                            <span class="text-stone-600 font-bold uppercase tracking-widest text-[10px]">Subtotal</span>
                            <span class="font-black text-stone-900">S/. {{ parseFloat(order.total).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b border-stone-200">
                            <span class="text-stone-600 font-bold uppercase tracking-widest text-[10px]">Envío</span>
                            <span class="font-black text-emerald-600">A convenir por WhatsApp</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-black text-stone-900 uppercase tracking-wider">Total</span>
                            <span class="text-3xl font-black text-amber-600">
                                S/. {{ parseFloat(order.total).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex flex-wrap gap-4 justify-between items-center">
                    <Link href="/orders" 
                          class="inline-flex items-center gap-2 bg-stone-900 text-white px-8 py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-amber-600 transition-all shadow-lg">
                        <span>←</span>
                        Volver a Mis Pedidos
                    </Link>
                    
                    <div class="flex gap-4">
                        <Link href="/" 
                              class="inline-flex items-center gap-2 bg-amber-600 text-white px-8 py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-stone-900 transition-all shadow-lg">
                            <span>🛒</span>
                            Seguir Comprando
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
