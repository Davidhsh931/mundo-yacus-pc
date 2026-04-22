<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    order: Object,
    whatsappNumber: String, // Número dinámico desde el controlador
    yapeNumber: String,     // Número Yape dinámico
    plinNumber: String,      // Número Plin dinámico
    timestamp: Number        // Forzar recarga cuando cambia
});

const formattedTotal = computed(() => {
    // Si order o total no existen, devuelve 0.00
    const total = props.order?.total || 0;
    return Number(total).toFixed(2);
});

const sendWhatsApp = (order) => {
    // Debug: Ver qué valor estamos usando
    console.log('📱 Debug OrderSuccess.vue:');
    console.log('- props.whatsappNumber:', props.whatsappNumber);
    console.log('- typeof props.whatsappNumber:', typeof props.whatsappNumber);
    
    const phone = props.whatsappNumber || "51987658914"; // Usar número dinámico o fallback actualizado
    console.log('- phone final:', phone);
    
    const message = encodeURIComponent(
        `¡Hola Mundo Yacus! 🐹\n\n` +
        `Acabo de realizar un pedido:\n` +
        `✅ *Pedido:* #${order.id}\n` +
        `💰 *Monto:* S/. ${order.total}\n` +
        `🏠 *Dirección:* ${order.shipping_address}\n` +
        `💳 *Método:* ${order.payment_method.toUpperCase()}\n\n` +
        `Adjunto el comprobante de pago aquí abajo. 👇` 
    );
    
    const whatsappLink = `https://wa.me/${phone}?text=${message}`;
    console.log('- whatsappLink:', whatsappLink);
    
    window.open(whatsappLink, '_blank');
};
</script>

<template>
    <Head title="Pedido Exitoso" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 px-2">
                <!-- Left: order info -->
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 tracking-wide mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600 inline-block"></span>
                        Pedido Confirmado
                    </span>
                    <h2 class="text-2xl font-medium text-gray-900 leading-tight">¡Pedido #{{ order.id }} Recibido!</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Tu pedido ha sido procesado exitosamente.</p>
                </div>

                <!-- Right: order total -->
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 text-right shrink-0">
                    <p class="text-[11px] font-medium text-red-700 tracking-wide">Total</p>
                    <p class="text-xl font-medium text-red-900 leading-tight">
                        Total: S/ {{ Number(order.total).toFixed(2) }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">
                
                <!-- Contenido Principal -->
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-6 text-center">
                        
                        <!-- Icono de Éxito -->
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-red-200">
                            <svg class="w-8 h-8 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </div>

                        <!-- Mensaje Principal -->
                        <h1 class="text-2xl font-medium text-gray-900 mb-3">¡Pedido Confirmado!</h1>
                        <p class="text-gray-600 mb-8 max-w-2xl mx-auto text-sm">
                            Tu pedido #{{ order.id }} ha sido recibido exitosamente. Estamos listos para preparar a tu nuevo compañero de Yacus.
                        </p>
                        
                        <!-- Detalles del Pedido -->
                        <div class="bg-red-50 border border-red-100 rounded-xl p-6 mb-8 text-left">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-3">
                                <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="1" y1="10" x2="19" y2="10"/>
                                </svg>
                                Instrucciones de Pago
                            </h3>
                            <div class="bg-white p-4 rounded-lg border border-red-100">
                                <p class="text-gray-700 mb-4 text-sm">
                                    Por favor, realiza el pago de <span class="text-lg font-medium text-red-700">S/. {{ order.total }}</span> vía 
                                    <span class="font-medium text-gray-900 bg-red-100 px-2 py-1 rounded text-xs">{{ order.payment_method }}</span>.
                                </p>
                                
                                <!-- Información de Pago Específico -->
                                <div class="mt-4">
                                    <div v-if="order.payment_method === 'yape'" class="bg-red-50 p-4 rounded-lg border border-red-100 text-center">
                                        <span class="text-2xl">🟣</span>
                                        <p class="font-medium text-red-700 mt-2">Pagar con Yape</p>
                                        <p class="text-sm text-red-600">{{ yapeNumber }}</p>
                                    </div>
                                    <div v-else-if="order.payment_method === 'plin'" class="bg-red-50 p-4 rounded-lg border border-red-100 text-center">
                                        <span class="text-2xl">🟢</span>
                                        <p class="font-medium text-red-700 mt-2">Pagar con Plin</p>
                                        <p class="text-sm text-red-600">{{ plinNumber }}</p>
                                    </div>
                                    <div v-else-if="order.payment_method === 'cash'" class="bg-red-50 p-4 rounded-lg border border-red-100 text-center">
                                        <span class="text-2xl">💵</span>
                                        <p class="font-medium text-red-700 mt-2">Pago en Efectivo</p>
                                        <p class="text-sm text-red-600">Entrega directa al recibir</p>
                                    </div>
                                    <div v-else-if="order.payment_method === 'transfer'" class="bg-red-50 p-4 rounded-lg border border-red-100 text-center">
                                        <span class="text-2xl">🏦</span>
                                        <p class="font-medium text-red-700 mt-2">Transferencia Bancaria</p>
                                        <p class="text-sm text-red-600">Coordina con el vendedor</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de WhatsApp -->
                        <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-8">
                            <h3 class="font-medium text-green-800 text-sm mb-3 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                    <path d="M5 12h14"/>
                                    <path d="M12 5l7 7-7 7"/>
                                </svg>
                                ¡Paso Final!
                            </h3>
                            <p class="text-green-700 mb-4 text-center max-w-md mx-auto text-sm">
                                Para confirmar tu pedido de <span class="font-medium text-green-800">S/. {{ order.total }}</span>, 
                                envía el comprobante de pago por WhatsApp.
                            </p>

                            <button 
                                @click="sendWhatsApp(order)"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 21l1.65-5.34A7.92 7.92 0 0 1 3 12a8 8 0 1 1 8 8 7.92 7.92 0 0 1-3.66-.89L3 21z"/>
                                    <path d="M9 10h.01M15 10h.01"/>
                                </svg>
                                Enviar Comprobante por WhatsApp
                            </button>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link href="/orders" 
                                  class="bg-red-700 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-800 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                                </svg>
                                Ver Mis Pedidos
                            </Link>
                            <Link href="/" 
                                  class="text-red-600 border border-red-200 bg-red-50 px-4 py-2 rounded-lg font-medium hover:bg-red-100 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"/>
                                    <circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                </svg>
                                Volver a la Tienda
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
