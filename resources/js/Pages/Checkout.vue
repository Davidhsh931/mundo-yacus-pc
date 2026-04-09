<script setup>
import { ref } from 'vue'; // <-- IMPORTANTE: Importar ref
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps({
    cart: Object,
    total: Number
});

const form = useForm({
    shipping_address: '',
    payment_method: 'yape', // Valor por defecto
});

const showConfirmModal = ref(false);
const errors = ref({});

const validateField = (field, value) => {
    errors.value[field] = '';
    
    switch(field) {
        case 'shipping_address':
            if (!value || value.trim().length < 10) {
                errors.value[field] = 'La dirección debe tener al menos 10 caracteres';
            } else if (value.trim().length > 255) {
                errors.value[field] = 'La dirección no puede exceder 255 caracteres';
            }
            break;
            
        case 'payment_method':
            if (!value) {
                errors.value[field] = 'Debes seleccionar un método de pago';
            }
            break;
    }
};

const validateForm = () => {
    validateField('shipping_address', form.shipping_address);
    validateField('payment_method', form.payment_method);
    
    return !Object.values(errors.value).some(error => error !== '');
};

const submit = () => {
    // Validar que el carrito no esté vacío
    if (!props.cart || Object.keys(props.cart).length === 0) {
        alert('❌ Tu carrito está vacío. Agrega productos antes de continuar.');
        return;
    }
    
    // Validar formulario con validación frontend
    if (!validateForm()) {
        return;
    }
    
    // Mostrar modal de confirmación
    showConfirmModal.value = true;
};

const confirmOrder = () => {
    showConfirmModal.value = false;
    form.post(route('checkout.process'));
};

const cancelOrder = () => {
    showConfirmModal.value = false;
};

const getPaymentMethodName = (method) => {
    const methods = {
        'yape': 'Yape 💜',
        'plin': 'Plin 💙', 
        'transfer': 'Transferencia 🏦',
        'cash': 'Efectivo 💵'
    };
    return methods[method] || method;
};
</script>

<template>
    <Head title="Finalizar Pedido" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 px-2">
                <!-- Left: checkout info -->
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 tracking-wide mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 inline-block"></span>
                        Proceso de Pago
                    </span>
                    <h2 class="text-2xl font-medium text-gray-900 leading-tight">Finalizar Pedido</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Completa tus datos para recibir el pedido.</p>
                </div>

                <!-- Right: total -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 text-right shrink-0">
                    <p class="text-[11px] font-medium text-amber-700 tracking-wide">Total</p>
                    <p class="text-xl font-medium text-amber-900 leading-tight">
                        S/. {{ total.toFixed(2) }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">
                
                <!-- Formulario Principal -->
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-6">
                        
                        <!-- Resumen del Carrito -->
                        <div class="mb-8">
                            <div class="bg-amber-50 border border-amber-100 rounded-xl p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-3">
                                    <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="21" r="1"/>
                                        <circle cx="20" cy="21" r="1"/>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                    </svg>
                                    Resumen del Pedido
                                </h3>
                                <div v-if="cart && Object.keys(cart).length > 0" class="space-y-2">
                                    <div v-for="(item, id) in cart" :key="id" 
                                         class="flex justify-between items-center bg-white p-3 rounded-lg border border-amber-100">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-600 font-medium text-sm">
                                                {{ item.quantity }}
                                            </div>
                                            <span class="font-medium text-gray-900 text-sm">{{ item.name }}</span>
                                        </div>
                                        <span class="font-medium text-gray-900 text-sm">S/. {{ (item.price * item.quantity).toFixed(2) }}</span>
                                    </div>
                                </div>
                                <div class="border-t border-amber-200 mt-4 pt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base font-medium text-gray-900">Total Final:</span>
                                        <span class="text-lg font-medium text-amber-700">S/. {{ total.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Dirección de Entrega -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                        <circle cx="12" cy="9" r="2.5"/>
                                    </svg>
                                    Dirección de Entrega en Yacus/Huánuco
                                </label>
                                <input 
                                    v-model="form.shipping_address" 
                                    @input="validateField('shipping_address', $event.target.value)"
                                    type="text" 
                                    placeholder="Ej: Jr. Libertad 123, frente a la plaza"
                                    :class="[
                                        'w-full border bg-gray-50 rounded-lg p-3 focus:ring-amber-500 focus:border-amber-500 text-gray-900 placeholder-gray-400 transition-colors text-sm',
                                        errors.shipping_address ? 'border-red-500 bg-red-50' : 'border-gray-200'
                                    ]" 
                                    required 
                                />
                                <div v-if="errors.shipping_address" class="mt-2 text-red-600 text-sm flex items-center gap-1">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    {{ errors.shipping_address }}
                                </div>
                            </div>

                            <!-- Método de Pago -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-4 text-center flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="1" y1="10" x2="19" y2="10"/>
                                    </svg>
                                    ¿Cómo le pagarás al productor?
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <label :class="{'border-amber-600 bg-amber-50': form.payment_method === 'yape'}" 
                                        @click="validateField('payment_method', 'yape')"
                                        class="border border-gray-200 p-3 rounded-lg cursor-pointer transition-all flex flex-col items-center hover:border-amber-300">
                                        <input type="radio" v-model="form.payment_method" value="yape" class="hidden" />
                                        <span class="text-xl mb-1"></span>
                                        <span class="font-medium text-sm text-gray-700">Yape</span>
                                    </label>

                                    <label :class="{'border-amber-600 bg-amber-50': form.payment_method === 'plin'}" 
                                        @click="validateField('payment_method', 'plin')"
                                        class="border border-gray-200 p-3 rounded-lg cursor-pointer transition-all flex flex-col items-center hover:border-amber-300">
                                        <input type="radio" v-model="form.payment_method" value="plin" class="hidden" />
                                        <span class="text-xl mb-1"></span>
                                        <span class="font-medium text-sm text-gray-700">Plin</span>
                                    </label>

                                    <label :class="{'border-amber-600 bg-amber-50': form.payment_method === 'cash'}" 
                                        @click="validateField('payment_method', 'cash')"
                                        class="border border-gray-200 p-3 rounded-lg cursor-pointer transition-all flex flex-col items-center hover:border-amber-300">
                                        <input type="radio" v-model="form.payment_method" value="cash" class="hidden" />
                                        <span class="text-xl mb-1"></span>
                                        <span class="font-medium text-sm text-gray-700">Efectivo</span>
                                    </label>

                                    <label :class="{'border-amber-600 bg-amber-50': form.payment_method === 'transfer'}" 
                                        @click="validateField('payment_method', 'transfer')"
                                        class="border border-gray-200 p-3 rounded-lg cursor-pointer transition-all flex flex-col items-center hover:border-amber-300">
                                        <input type="radio" v-model="form.payment_method" value="transfer" class="hidden" />
                                        <span class="text-xl mb-1"></span>
                                        <span class="font-medium text-sm text-gray-700">Transferencia</span>
                                    </label>
                                </div>
                                <div v-if="errors.payment_method" class="mt-3 text-red-600 text-sm flex items-center gap-1">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    {{ errors.payment_method }}
                                </div>
                            </div>

                            <!-- Total Final -->
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-gray-700 font-medium">Total a pagar al vendedor:</span>
                                    <span class="bg-amber-600 text-white px-3 py-1 rounded-full text-xs font-medium">SIN COMISIONES</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-2xl font-medium text-gray-900">S/. {{ total.toFixed(2) }}</span>
                                    <span class="text-sm bg-gray-200 px-3 py-1 rounded-full font-medium">Soles</span>
                                </div>
                                <p class="text-xs mt-3 text-amber-700 text-center font-medium">Pago directo al productor de Yacus</p>
                            </div>

                            <!-- Botón de Confirmación -->
                            <button type="submit" 
                                    :disabled="form.processing"
                                    class="w-full bg-amber-600 text-white py-3 rounded-lg font-medium hover:bg-amber-700 transition-colors flex items-center justify-center gap-2">
                                <svg v-if="form.processing" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M21 12a9 9 0 11-6.219-8.56"/>
                                </svg>
                                <svg v-else class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"/>
                                    <circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                </svg>
                                {{ form.processing ? 'Procesando...' : 'Confirmar Pedido' }}
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal de Confirmación -->
    <div v-if="showConfirmModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border-2 border-yellow-500">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">🛒</span>
                </div>
                <h3 class="text-2xl font-black text-gray-950 mb-2">¿Confirmar Pedido?</h3>
                <p class="text-gray-600 text-sm">Estás a punto de procesar tu pedido por:</p>
                <p class="text-3xl font-black text-yellow-600 mt-2">S/. {{ total.toFixed(2) }}</p>
            </div>

            <div class="space-y-3 mb-6 text-sm">
                <div class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    <span class="text-gray-700">Dirección: {{ form.shipping_address }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    <span class="text-gray-700">Método de pago: {{ getPaymentMethodName(form.payment_method) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-green-500">✓</span>
                    <span class="text-gray-700">{{ Object.keys(cart).length }} productos en el carrito</span>
                </div>
            </div>

            <div class="flex gap-3">
                <button @click="cancelOrder" 
                        class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-2xl font-black hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>
                <button @click="confirmOrder" 
                        :disabled="form.processing"
                        class="flex-1 bg-yellow-500 text-gray-950 py-3 px-6 rounded-2xl font-black hover:bg-gray-950 hover:text-yellow-400 transition-colors disabled:opacity-50">
                    <span v-if="form.processing">⏳ Procesando...</span>
                    <span v-else>✅ Confirmar Pedido</span>
                </button>
            </div>
        </div>
    </div>
</template>
