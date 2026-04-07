<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
    whatsappNumber: String, // Número dinámico desde el controlador
    yapeNumber: String,     // Número Yape dinámico
    plinNumber: String,      // Número Plin dinámico
    timestamp: Number        // Forzar recarga cuando cambia
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
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-950 p-8 rounded-3xl shadow-2xl border-b-8 border-yellow-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-green-500/10 text-green-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-green-500/20">
                        Pedido Confirmado
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        ¡Pedido #{{ order.id }} Recibido!
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Estamos listos para preparar a tu nuevo compañero.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-green-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total Pagado</span>
                    <p class="text-3xl font-black text-green-400 leading-none">S/. {{ order.total }}</p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
                
                <!-- Contenido Principal -->
                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 overflow-hidden border border-gray-100">
                    <div class="p-8 lg:p-12 text-center">
                        
                        <!-- Icono de Éxito -->
                        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center text-6xl mx-auto mb-8 border-4 border-green-200 shadow-lg">
                            ✅
                        </div>

                        <!-- Mensaje Principal -->
                        <h1 class="text-4xl font-black text-gray-950 mb-4 tracking-tight">¡Pedido Confirmado!</h1>
                        <p class="text-gray-600 text-lg mb-12 max-w-2xl mx-auto">
                            Tu pedido #{{ order.id }} ha sido recibido exitosamente. Estamos listos para preparar a tu nuevo compañero de Yacus.
                        </p>
                        
                        <!-- Detalles del Pedido -->
                        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl border border-yellow-100 p-8 mb-12 text-left">
                            <h3 class="text-lg font-black text-gray-950 mb-6 flex items-center gap-3">
                                <span class="text-2xl">💳</span>
                                Instrucciones de Pago
                            </h3>
                            <div class="bg-white p-6 rounded-xl border border-yellow-100">
                                <p class="text-gray-700 mb-4">
                                    Por favor, realiza el pago de <span class="text-2xl font-black text-yellow-600">S/. {{ order.total }}</span> vía 
                                    <span class="font-black text-gray-950 uppercase bg-yellow-100 px-3 py-1 rounded-lg">{{ order.payment_method }}</span>.
                                </p>
                                
                                <!-- Información de Pago Específico -->
                                <div class="mt-6">
                                    <div v-if="order.payment_method === 'yape'" class="bg-purple-50 p-6 rounded-xl border border-purple-100 text-center">
                                        <span class="text-3xl">💜</span>
                                        <p class="font-black text-purple-700 text-lg mt-2">Pagar con Yape</p>
                                        <p class="text-sm text-purple-600 mt-1">{{ yapeNumber }}</p>
                                    </div>
                                    <div v-else-if="order.payment_method === 'plin'" class="bg-blue-50 p-6 rounded-xl border border-blue-100 text-center">
                                        <span class="text-3xl">💙</span>
                                        <p class="font-black text-blue-700 text-lg mt-2">Pagar con Plin</p>
                                        <p class="text-sm text-blue-600 mt-1">{{ plinNumber }}</p>
                                    </div>
                                    <div v-else-if="order.payment_method === 'cash'" class="bg-green-50 p-6 rounded-xl border border-green-100 text-center">
                                        <span class="text-3xl">💵</span>
                                        <p class="font-black text-green-700 text-lg mt-2">Pago en Efectivo</p>
                                        <p class="text-sm text-green-600 mt-1">Entrega directa al recibir</p>
                                    </div>
                                    <div v-else-if="order.payment_method === 'transfer'" class="bg-orange-50 p-6 rounded-xl border border-orange-100 text-center">
                                        <span class="text-3xl">🏦</span>
                                        <p class="font-black text-orange-700 text-lg mt-2">Transferencia Bancaria</p>
                                        <p class="text-sm text-orange-600 mt-1">Coordina con el vendedor</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de WhatsApp -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border-2 border-dashed border-green-200 p-8 mb-12">
                            <h3 class="font-black text-green-800 uppercase text-sm mb-4 flex items-center justify-center gap-2">
                                <span>🚀</span> ¡Paso Final!
                            </h3>
                            <p class="text-green-700 mb-6 text-center max-w-md mx-auto">
                                Para confirmar tu pedido de <span class="font-black text-green-800">S/. {{ order.total }}</span>, 
                                envía el comprobante de pago por WhatsApp.
                            </p>

                            <button 
                                @click="sendWhatsApp(order)"
                                class="w-full bg-[#25D366] hover:bg-[#128C7E] text-white font-black py-6 px-8 rounded-2xl shadow-2xl transition-all flex items-center justify-center gap-4 transform hover:scale-105 active:scale-95"
                            >
                                <span class="text-3xl">📱</span>
                                <span class="text-xl">ENVIAR COMPROBANTE POR WHATSAPP</span>
                            </button>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex flex-col sm:flex-row gap-6 justify-center">
                            <Link href="/orders" 
                                  class="bg-yellow-500 text-gray-950 px-8 py-4 rounded-2xl font-black hover:bg-gray-950 hover:text-yellow-400 transition shadow-lg hover:shadow-yellow-100 flex items-center justify-center gap-2">
                                <span>📦</span>
                                Ver Mis Pedidos
                            </Link>
                            <Link href="/" 
                                  class="bg-gray-100 text-gray-700 px-8 py-4 rounded-2xl font-black hover:bg-gray-200 transition flex items-center justify-center gap-2">
                                <span>🛍️</span>
                                Volver a la Tienda
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
