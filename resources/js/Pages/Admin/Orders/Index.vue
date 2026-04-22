<script setup>
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    orders: Array,
    adminContext: String, // 'global' indica contexto de administración
    success: String
});

const editingOrder = ref(null);
const editForm = useForm({
    status: '',
    delivery_type: '',
    shipping_cost: 0
});

const startEdit = (order) => {
    console.log('Iniciando edición para pedido:', order.id);
    editingOrder.value = order.id;
    editForm.status = order.status;
    editForm.delivery_type = order.delivery_type || 'recojo';
    editForm.shipping_cost = parseFloat(order.shipping_cost || 0);
};

const calculateSubtotal = (order) => {
    return order.items.reduce((total, item) => total + (item.unit_price * item.quantity), 0);
};

const calculateTotal = (order, shippingCost) => {
    return calculateSubtotal(order) + parseFloat(shippingCost || 0);
};

const cancelEdit = () => {
    editingOrder.value = null;
    editForm.reset();
};

const saveEdit = (orderId) => {
    console.log('Guardando edición para pedido:', orderId);

    // Si shipping_cost es 0 y delivery_type no es recojo, cambiar a recojo
    if (editForm.shipping_cost === 0 && editForm.delivery_type !== 'recojo') {
        console.log('Shipping cost es 0, cambiando delivery_type a recojo');
        editForm.delivery_type = 'recojo';
    }

    console.log('Datos a enviar:', {
        status: editForm.status,
        delivery_type: editForm.delivery_type,
        shipping_cost: editForm.shipping_cost
    });

    editForm.patch(route('admin.orders.update', orderId), {
        onSuccess: (page) => {
            console.log('Edición guardada exitosamente');
            console.log('Datos actualizados en la página:', page.props.orders);
            editingOrder.value = null;
            editForm.reset();
        },
        onError: (errors) => {
            console.error('Errores al guardar:', JSON.stringify(errors, null, 2));
            console.error('Formulario errors:', editForm.errors);
        }
    });
};

const updateStatus = (orderId, newStatus) => {
    const form = useForm({ status: newStatus });
    form.patch(route('admin.orders.update', orderId));
};

const getStatusColor = (status) => {
    const colors = {
        'pending': 'bg-red-100 text-red-800',
        'paid': 'bg-red-100 text-red-800',
        'shipped': 'bg-red-100 text-red-800',
        'delivered': 'bg-red-100 text-red-800',
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

const getNextStatus = (currentStatus) => {
    const flow = {
        'pending': 'paid',
        'paid': 'shipped',
        'shipped': 'delivered'
    };
    return flow[currentStatus];
};

const getNextAction = (currentStatus) => {
    const actions = {
        'pending': '✅ Confirmar Pago',
        'paid': '📦 Preparar Envío',
        'shipped': '🚚 Enviar Pedido'
    };
    return actions[currentStatus];
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

const getTotalItems = (order) => {
    return order.items.reduce((total, item) => total + item.quantity, 0);
};
</script>

<template>
    <Head title="Gestión de Ventas - Administración" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 p-8 rounded-3xl shadow-2xl border-b-4 border-red-700">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-red-700/10 text-red-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-red-700/20">
                        Ventas
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Gestión de Órdenes
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Control y seguimiento de pedidos.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-red-700 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total</span>
                    <p class="text-2xl font-black text-red-400 leading-none">{{ orders?.length || 0 }} <span class="text-xs text-red-600">órdenes</span></p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">

                <transition enter-active-class="transition ease-out duration-300" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100">
                    <div v-if="success" class="mb-8 bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-xl shadow-sm flex items-center gap-3">
                        <span class="text-xl">✨</span>
                        <p class="font-bold text-sm">{{ success }}</p>
                    </div>
                </transition>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="(stat, index) in [
                        { label: 'Total Pedidos', value: orders.length, icon: '📊', color: 'blue' },
                        { label: 'Pendientes', value: orders.filter(o => o.status === 'pending').length, icon: '⏳', color: 'amber' },
                        { label: 'Entregados', value: orders.filter(o => o.status === 'delivered').length, icon: '✅', color: 'emerald' },
                        { label: 'Ingresos Totales', value: `S/. ${orders.reduce((sum, o) => sum + parseFloat(o.total || 0), 0).toFixed(2)}`, icon: '💎', color: 'purple' }
                    ]" :key="index" 
                    class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 group">
                        <div class="flex items-center gap-4">
                            <div :class="`bg-${stat.color}-100 text-${stat.color}-600 group-hover:bg-${stat.color}-200 group-hover:scale-110 transition-all`" class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                                {{ stat.icon }}
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ stat.label }}</p>
                                <p class="text-xl font-black text-slate-800">{{ stat.value }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="orders.length > 0" class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                            <span class="text-2xl">📋</span> Lista de Órdenes
                        </h3>
                        <p class="text-slate-500 text-xs mt-1">Historial completo de pedidos</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="p-6 font-black uppercase text-[10px] text-slate-400 tracking-widest">Orden</th>
                                    <th class="p-6 font-black uppercase text-[10px] text-slate-400 tracking-widest">Cliente</th>
                                    <th class="p-6 font-black uppercase text-[10px] text-slate-400 tracking-widest text-center">Items</th>
                                    <th class="p-6 font-black uppercase text-[10px] text-slate-400 tracking-widest">Total</th>
                                    <th class="p-6 font-black uppercase text-[10px] text-slate-400 tracking-widest">Entrega</th>
                                    <th class="p-6 font-black uppercase text-[10px] text-slate-400 tracking-widest">Estado Actual</th>
                                    <th class="p-6 font-black uppercase text-[10px] text-slate-400 tracking-widest text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="order in orders" :key="order.id" class="group hover:bg-red-50/30 transition-all">
                                    <td class="p-6">
                                        <div class="font-black text-slate-800">#{{ order.id }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                            {{ new Date(order.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' }) }}
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 font-black text-[10px]">
                                                {{ order.user?.name?.substring(0,2).toUpperCase() || '??' }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-black text-slate-800">{{ order.user?.name || 'N/A' }}</div>
                                                <div class="text-[10px] text-slate-400 font-medium">{{ order.user?.email || 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6 text-center font-black text-slate-600">
                                        <span class="bg-red-100 px-3 py-1 rounded-xl text-xs text-red-700">
                                            {{ getTotalItems(order) }}
                                        </span>
                                    </td>
                                    <td class="p-6">
                                        <div class="text-sm font-black text-slate-900 italic">
                                            S/. {{ parseFloat(order.total || 0).toFixed(2) }}
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <div v-if="editingOrder !== order.id">
                                            <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase border tracking-wider inline-block bg-red-50 text-red-700 border-red-200">
                                                {{ getDeliveryTypeText(order.delivery_type || 'recojo') }}
                                            </span>
                                        </div>
                                        <div v-else class="space-y-2">
                                            <select v-model="editForm.delivery_type"
                                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[10px] font-black uppercase tracking-tight text-slate-700 focus:ring-2 focus:ring-red-700">
                                                <option value="recojo">Recojo</option>
                                                <option value="envio_domicilio">Envío a Domicilio</option>
                                                <option value="envio_express">Envío Express</option>
                                            </select>
                                            <div class="p-2 bg-slate-50 rounded-lg text-[10px]">
                                                <div class="flex justify-between mb-1">
                                                    <span class="text-slate-500">Subtotal:</span>
                                                    <span class="font-medium text-slate-800">S/. {{ calculateSubtotal(order).toFixed(2) }}</span>
                                                </div>
                                                <div class="flex justify-between items-center gap-2">
                                                    <span class="text-slate-500">Envío:</span>
                                                    <input type="number"
                                                           v-model="editForm.shipping_cost"
                                                           step="0.01"
                                                           :disabled="editForm.delivery_type === 'recojo'"
                                                           :class="editForm.delivery_type === 'recojo' ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-white'"
                                                           class="w-20 px-2 py-1 border border-slate-200 rounded text-[10px] font-black text-slate-700 focus:ring-2 focus:ring-red-700"
                                                           placeholder="0" />
                                                </div>
                                                <div class="flex justify-between mt-1 pt-1 border-t border-slate-200">
                                                    <span class="font-black text-slate-700">Total:</span>
                                                    <span class="font-black text-red-600">S/. {{ calculateTotal(order, editForm.shipping_cost).toFixed(2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase border tracking-wider inline-block" 
                                              :class="getStatusColor(order.status)">
                                            {{ getStatusText(order.status) }}
                                        </span>
                                    </td>
                                    <td class="p-6">
                                        <div class="flex items-center justify-end gap-3">
                                            <Link :href="route('admin.orders.show', order.id)"
                                                  class="p-2 bg-red-100 hover:bg-red-200 rounded-full transition-colors group">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 group-hover:text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </Link>

                                            <button v-if="editingOrder !== order.id"
                                                    @click="startEdit(order)"
                                                    class="p-2 bg-red-100 hover:bg-red-200 rounded-full transition-colors group"
                                                    title="Editar pedido">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 group-hover:text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            <div v-if="editingOrder === order.id" class="flex items-center gap-2">
                                                <button @click="() => { console.log('Botón guardar clickeado para pedido:', order.id); saveEdit(order.id); }"
                                                        :disabled="editForm.processing"
                                                        class="p-2 bg-red-100 hover:bg-red-200 rounded-full transition-colors group"
                                                        title="Guardar cambios">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 group-hover:text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                                <button @click="cancelEdit"
                                                        class="p-2 bg-red-100 hover:bg-red-200 rounded-full transition-colors group"
                                                        title="Cancelar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 group-hover:text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div v-if="editingOrder !== order.id" class="relative flex items-center">
                                                <select v-model="order.status"
                                                        @change="updateStatus(order.id, $event.target.value)"
                                                        class="appearance-none bg-red-50 border-none rounded-xl py-2 pl-3 pr-8 text-[10px] font-black uppercase tracking-tight text-red-700 focus:ring-2 focus:ring-red-700 cursor-pointer shadow-sm">
                                                    <option value="pending">Pendiente</option>
                                                    <option value="paid">Pagado</option>
                                                    <option value="shipped">Enviado</option>
                                                    <option value="delivered">Entregado</option>
                                                    <option value="canceled">Cancelado</option>
                                                </select>
                                                <div class="absolute right-2 pointer-events-none text-slate-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else class="text-center py-24 bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200">
                    <div class="text-7xl mb-6 grayscale opacity-40">📦</div>
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-widest">Sin registros</h3>
                    <p class="text-slate-400 font-medium mt-2">No hay pedidos registrados en el sistema actualmente.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>