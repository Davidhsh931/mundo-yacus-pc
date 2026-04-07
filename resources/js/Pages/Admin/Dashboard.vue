<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import SalesChart from '@/Components/SalesChart.vue';

import CreateProductForm from '@/Pages/Admin/CreateProduct.vue'; // Recuperamos el formulario

import { ref, onMounted } from 'vue';

import { Head, Link } from '@inertiajs/vue3';

import axios from 'axios';



const props = defineProps({

    totalPigs: { type: Number, default: 0 },

    sales: { type: Number, default: 0 },

    stats: {

        type: Object,

        default: () => ({

            stock_sugerido: 'Calculando...',

            chart_data: {

                values: Array(30).fill(0),

                labels: Array.from({length: 30}, (_, i) => String(i + 1))

            }

        })

    },

    latestProducts: { type: Array, default: () => [] },

    lowStockProducts: { type: Array, default: () => [] },

    lowStockCount: { type: Number, default: 0 }

});



const stats = ref({

    stock_sugerido: props.stats?.stock_sugerido || 'Calculando...',

    chart_data: props.stats?.chart_data || {

        values: Array(30).fill(0),

        labels: Array.from({length: 30}, (_, i) => String(i + 1))

    }

});

const isLoading = ref(true);



// ESTA ES LA LÓGICA QUE HACE QUE EL BOTÓN NO SEA ADORNO

const ejecutarIA = async () => {

    isLoading.value = true;

    try {

        const res = await axios.get('/api/cuy/sugerir-stock/1');

       

        // El nuevo formato es simple: { values: [], labels: [] }

        if (res.data && res.data.values && res.data.labels) {

            // Usar directamente los datos del backend

            stats.value.chart_data = {

                values: res.data.values,

                labels: res.data.labels

            };

           

            // Calcular stock sugerido simple

            const totalVentas = res.data.values.reduce((a, b) => a + b, 0);

            stats.value.stock_sugerido = totalVentas > 0 ? Math.ceil(totalVentas * 1.1) : 'Calculando...';

           

            // Mostrar mensaje de éxito si existe (validar que exista antes de acceder)

            if (res.data.success !== undefined && res.data.success) {

                // Éxito silencioso

            }

        } else {

            // Respuesta inesperada silenciosa

        }

       

    } catch (e) {

        // Valores por defecto en caso de error

        stats.value = {

            stock_sugerido: 'Error de conexión',

            chart_data: {

                values: Array(30).fill(0),

                labels: Array.from({length: 30}, (_, i) => String(i + 1))

            }

        };

    } finally {

        isLoading.value = false;

    }

};



onMounted(() => {

    ejecutarIA();

});

</script>



<template>
    <Head title="Panel de Administración" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 p-8 rounded-3xl shadow-2xl border-b-4 border-emerald-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-emerald-500/20">
                        Centro de Control
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Panel Administrativo
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Gestión integral de Mundo Yacus.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-emerald-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Estado</span>
                    <p class="text-2xl font-black text-emerald-400 leading-none">Sistema <span class="text-xs text-emerald-600">Online</span></p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 shadow-sm hover:shadow-lg transition-all duration-500 rounded-2xl border border-slate-200 group">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-3 bg-blue-100 rounded-xl text-blue-600 text-xl group-hover:bg-blue-200 transition-colors">🐹</span>
                            <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Total</span>
                        </div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Población Total</h3>
                        <p class="text-3xl font-black text-gray-900">{{ totalPigs }}</p>
                        <span class="text-xs text-gray-400">ejemplares registrados</span>
                    </div>
                    
                    <div class="bg-white p-6 shadow-sm hover:shadow-lg transition-all duration-500 rounded-2xl border border-slate-200 group">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-3 bg-purple-100 rounded-xl text-purple-600 text-xl group-hover:bg-purple-200 transition-colors">🤖</span>
                            <button @click="ejecutarIA" class="text-gray-400 hover:text-purple-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Proyección Stock (IA)</h3>
                        <p class="text-3xl font-black text-purple-700">{{ stats.stock_sugerido }}</p>
                        <span class="text-xs text-gray-400">unidades sugeridas</span>
                    </div>

                    <div class="bg-white p-6 shadow-sm hover:shadow-lg transition-all duration-500 rounded-2xl border border-slate-200 group">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-3 bg-orange-100 rounded-xl text-orange-600 text-xl group-hover:bg-orange-200 transition-colors">⚠️</span>
                            <span class="text-[10px] font-black text-orange-600 uppercase tracking-widest">Alerta</span>
                        </div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Stock Bajo</h3>
                        <p class="text-3xl font-black text-orange-600">{{ lowStockCount }}</p>
                        <span class="text-xs text-gray-400">productos críticos</span>
                    </div>

                    <div class="bg-white p-6 shadow-sm hover:shadow-lg transition-all duration-500 rounded-2xl border border-slate-200 group">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-3 bg-emerald-100 rounded-xl text-emerald-600 text-xl group-hover:bg-emerald-200 transition-colors">💰</span>
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Ventas</span>
                        </div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Ingresos Periodo</h3>
                        <p class="text-3xl font-black text-emerald-700">S/ {{ sales }}</p>
                        <span class="text-xs text-gray-400">soles generados</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                                <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                                    <span class="text-2xl">➕</span> Gestión de Inventario
                                </h3>
                                <p class="text-slate-500 text-xs mt-1">Añadir nuevos productos al sistema</p>
                            </div>
                            <div class="p-8">
                                <CreateProductForm />
                            </div>
                        </div>

                        <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                                        📊 Análisis de Tendencia
                                    </h3>
                                    <p class="text-slate-500 text-xs mt-1">Ventas vs Tiempo</p>
                                </div>
                                <span class="text-[10px] bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full font-black uppercase tracking-widest">IA</span>
                            </div>
                            <div v-if="isLoading" class="h-64 flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
                            </div>
                            <div v-else class="h-72">
                                <SalesChart 
                                    v-if="stats.chart_data?.values?.length > 0" 
                                    :data="stats.chart_data.values" 
                                    :labels="stats.chart_data.labels" 
                                />
                                <div v-else class="h-full flex items-center justify-center text-slate-400">
                                    <p class="text-sm italic">Sin datos suficientes para generar proyección...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="bg-slate-900 text-white p-8 rounded-2xl shadow-md border-b-4 border-emerald-500">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-2xl">🧠</span>
                                <h3 class="font-black text-lg">IA Machine Learning</h3>
                            </div>
                            <p class="text-slate-300 text-sm mb-6 leading-relaxed">Mejora la precisión de categorización entrenando el modelo con nuevos parámetros.</p>
                            <Link 
                                :href="route('ai-training.index')" 
                                class="block text-center bg-emerald-500 text-slate-900 py-3 rounded-xl font-black text-sm hover:bg-slate-900 hover:text-emerald-400 transition-all shadow-lg"
                            >
                                Gestionar Conocimiento
                            </Link>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 p-6 space-y-6">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Configuración General</h3>
                            
                            <Link href="/admin/events" class="flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                                <div class="flex items-center gap-4">
                                    <span class="bg-emerald-100 p-3 rounded-xl group-hover:bg-emerald-200 group-hover:scale-110 transition-all">📢</span>
                                    <div>
                                        <span class="text-sm font-black text-slate-700">Portal de Noticias</span>
                                        <p class="text-xs text-slate-500">Gestionar eventos y comunicados</p>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </Link>

                            <Link href="/admin/settings" class="flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                                <div class="flex items-center gap-4">
                                    <span class="bg-slate-100 p-3 rounded-xl group-hover:bg-slate-200 group-hover:scale-110 transition-all">⚙️</span>
                                    <div>
                                        <span class="text-sm font-black text-slate-700">Ajustes del Sistema</span>
                                        <p class="text-xs text-slate-500">Configuración general</p>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </Link>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
                                    <span class="text-lg">🕐</span> Actividad Reciente
                                </h3>
                            </div>
                            <div class="p-4">
                                <div v-if="latestProducts.length > 0" class="divide-y divide-slate-50">
                                    <Link v-for="product in latestProducts" 
                                          :key="product.id" 
                                          :href="`/product/${product.id}`"
                                          class="flex items-center p-3 hover:bg-slate-50 rounded-xl transition-colors group">
                                        <img v-if="product.images && product.images.length > 0" 
                                             :src="`/storage/${product.images[0].image_path}`" 
                                             class="w-12 h-12 object-cover rounded-xl border border-slate-100">
                                        <div v-else class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-xs text-slate-400">
                                            📷
                                        </div>
                                        <div class="ml-4 flex-1 min-w-0">
                                            <p class="text-sm font-black text-slate-800 truncate group-hover:text-emerald-600 transition-colors">{{ product.name }}</p>
                                            <p class="text-[10px] text-slate-500 uppercase tracking-wider">Stock: {{ product.stock }} | S/ {{ product.price }}</p>
                                        </div>
                                        <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ new Date(product.created_at).toLocaleDateString() }}</span>
                                    </Link>
                                </div>
                                <div v-else class="text-center py-8">
                                    <div class="text-4xl mb-2">📭</div>
                                    <p class="text-xs text-slate-400 font-black uppercase tracking-widest">No hay registros</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

