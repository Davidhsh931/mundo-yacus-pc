<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ 
    orders: Array,
    adminContext: String, // 'global' indica contexto de administración
    success: String
});

const updateStatus = (orderId, newStatus) => {
    const form = useForm({ status: newStatus });
    form.patch(route('admin.orders.update', orderId));
};

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

const getTotalItems = (order) => {
    return order.items.reduce((total, item) => total + item.quantity, 0);
};
</script>

<template>
    <Head title="Gestión de Ventas - Administración" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 p-8 rounded-3xl shadow-2xl border-b-4 border-emerald-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-emerald-500/20">
                        Ventas
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Gestión de Órdenes
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Control y seguimiento de pedidos.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-emerald-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total</span>
                    <p class="text-2xl font-black text-emerald-400 leading-none">{{ orders?.length || 0 }} <span class="text-xs text-emerald-600">órdenes</span></p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">

                <transition enter-active-class="transition ease-out duration-300" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100">
                    <div v-if="success" class="mb-8 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl shadow-sm flex items-center gap-3">
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
                                    <th class="p-6 font-black uppercase text-[10px] text-slate-400 tracking-widest">Estado Actual</th>
                                    <th class="p-6 font-black uppercase text-[10px] text-slate-400 tracking-widest text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="order in orders" :key="order.id" class="group hover:bg-emerald-50/30 transition-all">
                                    <td class="p-6">
                                        <div class="font-black text-slate-800">#{{ order.id }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                            {{ new Date(order.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' }) }}
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-black text-[10px]">
                                                {{ order.user?.name?.substring(0,2).toUpperCase() || '??' }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-black text-slate-800">{{ order.user?.name || 'N/A' }}</div>
                                                <div class="text-[10px] text-slate-400 font-medium">{{ order.user?.email || 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6 text-center font-black text-slate-600">
                                        <span class="bg-emerald-100 px-3 py-1 rounded-xl text-xs text-emerald-700">
                                            {{ getTotalItems(order) }}
                                        </span>
                                    </td>
                                    <td class="p-6">
                                        <div class="text-sm font-black text-slate-900 italic">
                                            S/. {{ parseFloat(order.total || 0).toFixed(2) }}
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
                                                  class="p-2 bg-emerald-100 hover:bg-emerald-200 rounded-full transition-colors group">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 group-hover:text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </Link>
                                            
                                            <div class="relative flex items-center">
                                                <select v-model="order.status" 
                                                        @change="updateStatus(order.id, $event.target.value)"
                                                        class="appearance-none bg-emerald-50 border-none rounded-xl py-2 pl-3 pr-8 text-[10px] font-black uppercase tracking-tight text-emerald-700 focus:ring-2 focus:ring-emerald-500 cursor-pointer shadow-sm">
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