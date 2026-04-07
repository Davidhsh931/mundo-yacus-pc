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
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-950 p-8 rounded-3xl shadow-2xl border-b-8 border-yellow-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-yellow-500/20">
                        Proceso de Pago
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Finalizar Pedido
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Completa tus datos para recibir el pedido.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-yellow-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total</span>
                    <p class="text-3xl font-black text-yellow-400 leading-none">S/. {{ total.toFixed(2) }}</p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
                
                <!-- Formulario Principal -->
                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 overflow-hidden border border-gray-100">
                    <div class="p-8 lg:p-12">
                        
                        <!-- Resumen del Carrito -->
                        <div class="mb-12">
                            <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl border border-yellow-100 p-8">
                                <h3 class="text-lg font-black text-gray-950 mb-6 flex items-center gap-3">
                                    <span class="text-2xl">🛒</span>
                                    Resumen del Pedido
                                </h3>
                                <div v-if="cart && Object.keys(cart).length > 0" class="space-y-3">
                                    <div v-for="(item, id) in cart" :key="id" 
                                         class="flex justify-between items-center bg-white p-4 rounded-xl border border-yellow-100">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600 font-black text-sm">
                                                {{ item.quantity }}
                                            </div>
                                            <span class="font-black text-gray-950">{{ item.name }}</span>
                                        </div>
                                        <span class="font-black text-gray-950">S/. {{ (item.price * item.quantity).toFixed(2) }}</span>
                                    </div>
                                </div>
                                <div class="border-t-2 border-yellow-200 mt-6 pt-6">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-black text-gray-950">Total Final:</span>
                                        <span class="text-2xl font-black text-yellow-600">S/. {{ total.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Dirección de Entrega -->
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                                    <span>📍</span> Dirección de Entrega en Yacus/Huánuco
                                </label>
                                <input 
                                    v-model="form.shipping_address" 
                                    @input="validateField('shipping_address', $event.target.value)"
                                    type="text" 
                                    placeholder="Ej: Jr. Libertad 123, frente a la plaza"
                                    :class="[
                                        'w-full border-2 bg-gray-50 rounded-2xl p-4 focus:ring-yellow-500 focus:border-yellow-500 font-black text-gray-950 placeholder-gray-400 transition-colors',
                                        errors.shipping_address ? 'border-red-500 bg-red-50' : 'border-gray-200'
                                    ]" 
                                    required 
                                />
                                <div v-if="errors.shipping_address" class="mt-2 text-red-600 text-sm font-medium flex items-center gap-1">
                                    <span>❌</span>
                                    {{ errors.shipping_address }}
                                </div>
                            </div>

                            <!-- Método de Pago -->
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-4 text-center flex items-center justify-center gap-2">
                                    <span>💳</span> ¿Cómo le pagarás al productor?
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <label :class="{'border-yellow-600 bg-yellow-50': form.payment_method === 'yape'}" 
                                        @click="validateField('payment_method', 'yape')"
                                        class="border-2 border-gray-200 p-4 rounded-2xl cursor-pointer transition-all flex flex-col items-center hover:border-yellow-300">
                                        <input type="radio" v-model="form.payment_method" value="yape" class="hidden" />
                                        <span class="text-2xl mb-2">💜</span>
                                        <span class="font-black text-sm uppercase text-gray-700">Yape</span>
                                    </label>

                                    <label :class="{'border-yellow-600 bg-yellow-50': form.payment_method === 'plin'}" 
                                        @click="validateField('payment_method', 'plin')"
                                        class="border-2 border-gray-200 p-4 rounded-2xl cursor-pointer transition-all flex flex-col items-center hover:border-yellow-300">
                                        <input type="radio" v-model="form.payment_method" value="plin" class="hidden" />
                                        <span class="text-2xl mb-2">💙</span>
                                        <span class="font-black text-sm uppercase text-gray-700">Plin</span>
                                    </label>

                                    <label :class="{'border-yellow-600 bg-yellow-50': form.payment_method === 'cash'}" 
                                        @click="validateField('payment_method', 'cash')"
                                        class="border-2 border-gray-200 p-4 rounded-2xl cursor-pointer transition-all flex flex-col items-center hover:border-yellow-300">
                                        <input type="radio" v-model="form.payment_method" value="cash" class="hidden" />
                                        <span class="text-2xl mb-2">💵</span>
                                        <span class="font-black text-sm uppercase text-gray-700">Efectivo</span>
                                    </label>

                                    <label :class="{'border-yellow-600 bg-yellow-50': form.payment_method === 'transfer'}" 
                                        @click="validateField('payment_method', 'transfer')"
                                        class="border-2 border-gray-200 p-4 rounded-2xl cursor-pointer transition-all flex flex-col items-center hover:border-yellow-300">
                                        <input type="radio" v-model="form.payment_method" value="transfer" class="hidden" />
                                        <span class="text-2xl mb-2">🏦</span>
                                        <span class="font-black text-sm uppercase text-gray-700">Transferencia</span>
                                    </label>
                                </div>
                                <div v-if="errors.payment_method" class="mt-4 text-red-600 text-sm font-medium flex items-center gap-1">
                                    <span>❌</span>
                                    {{ errors.payment_method }}
                                </div>
                            </div>

                            <!-- Total Final -->
                            <div class="bg-gray-950 text-white p-8 rounded-3xl shadow-2xl border border-yellow-500/20">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-gray-300 font-medium">Total a pagar al vendedor:</span>
                                    <span class="bg-yellow-500 text-gray-950 px-3 py-1 rounded-full text-xs font-black">SIN COMISIONES</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-4xl font-black">S/. {{ total.toFixed(2) }}</span>
                                    <span class="text-sm bg-white/20 px-4 py-2 rounded-full font-black">Soles</span>
                                </div>
                                <p class="text-xs mt-4 text-yellow-200 text-center font-medium">Pago directo al productor de Yacus</p>
                            </div>

                            <!-- Botón de Confirmación -->
                            <button type="submit" 
                                    :disabled="form.processing"
                                    class="w-full bg-yellow-500 text-gray-950 py-6 rounded-[2rem] font-black text-xl hover:bg-gray-950 hover:text-yellow-400 transition shadow-2xl shadow-yellow-100 active:scale-95 flex items-center justify-center gap-3">
                                <span v-if="form.processing">⏳</span>
                                <span v-else>🛒</span>
                                {{ form.processing ? 'Procesando...' : 'Revisar Pedido' }}
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
