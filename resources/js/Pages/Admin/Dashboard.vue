<template>
    <Head title="Panel de Administración" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 px-2">
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 tracking-wide mb-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        Centro de Control · Mundo Yacus
                    </span>
                    <h2 class="text-2xl font-medium text-gray-900 leading-tight">Panel Administrativo</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Gestión integral de alto rendimiento.</p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-[#F8FAFC] min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 rounded-2xl border border-gray-100 group">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-3 bg-slate-50 rounded-xl text-xl group-hover:bg-red-50 transition-colors">🐹</span>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Estadística</span>
                        </div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Población Total</h3>
                        <p class="text-3xl font-black text-slate-900">{{ totalPigs }}</p>
                        <span class="text-xs text-gray-400 italic">unidades en granja</span>
                    </div>
                    
                    <div class="bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 rounded-2xl border border-gray-100 group">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-3 bg-red-50 rounded-xl text-red-600 text-xl group-hover:bg-red-100 transition-colors">🤖</span>
                            <button @click="ejecutarIA" class="text-gray-300 hover:text-red-600 transition-all hover:rotate-180 duration-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Proyección Stock</h3>
                        <div class="flex items-baseline gap-2">
                            <p class="text-3xl font-black text-slate-900">{{ stockSugerido }}</p>
                            <span class="text-[10px] px-2 py-0.5 bg-red-100 text-red-700 rounded-full font-bold">IA</span>
                        </div>
                    </div>

                    <div :class="[
                        'p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 rounded-2xl border group',
                        lowStockCount > 0 ? 'bg-rose-50/30 border-rose-100' : 'bg-white border-gray-100'
                    ]">
                        <div class="flex items-center justify-between mb-4">
                            <span :class="[
                                'p-3 rounded-xl text-xl transition-colors',
                                lowStockCount > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-50 text-gray-400'
                            ]">⚠️</span>
                            <span v-if="lowStockCount > 0" class="flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                            </span>
                        </div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Stock Crítico</h3>
                        <p :class="['text-3xl font-black', lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900']">
                            {{ lowStockCount }}
                        </p>
                        <span class="text-xs text-gray-400 uppercase tracking-tighter font-medium">Requiere atención</span>
                    </div>

                    <div class="bg-slate-900 p-6 shadow-xl hover:-translate-y-1 transition-all duration-300 rounded-2xl border border-slate-800 group">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-3 bg-slate-800 rounded-xl text-red-400 text-xl group-hover:scale-110 transition-transform">💰</span>
                        </div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase mb-2 leading-none">Ingresos Periodo</h3>
                        <p class="text-3xl font-black text-white leading-tight">{{ ingresosFormateados }}</p>
                        <div class="mt-2 flex items-center gap-1 text-red-400 text-[10px] font-bold uppercase">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                            </svg>
                            Rendimiento Positivo
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        
                        <div :class="['relative overflow-hidden border rounded-3xl p-6 transition-all duration-500 shadow-sm group', analisisIA.clase]">
                            <div class="relative flex items-start gap-4">
                                <div class="flex-shrink-0 mt-1 text-2xl">
                                    {{ analisisIA.icon }}
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <h4 :class="['text-[10px] font-black uppercase tracking-[0.2em]', analisisIA.colorText]">
                                            {{ analisisIA.titulo }}
                                        </h4>
                                        <span class="h-[1px] flex-1 bg-current opacity-10"></span>
                                    </div>

                                    <p class="text-slate-700 text-sm leading-relaxed font-medium mb-4">
                                        {{ analisisIA.mensaje }}
                                    </p>
                                    
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-4 border-t border-current border-opacity-5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[9px] bg-white px-2 py-1 rounded shadow-sm font-bold uppercase tracking-tighter">Tip de Gestión</span>
                                            <p class="text-xs text-slate-500 italic">{{ analisisIA.recomendacion }}</p>
                                        </div>
                                        
                                        <button 
                                        @click="ejecutarAccionIA(analisisIA.tipoAccion)"
                                        :class="['px-6 py-2.5 rounded-xl text-white text-xs font-bold transition-all shadow-lg active:scale-95', analisisIA.btnClase]"
                                    >
                                        {{ analisisIA.btnText }}
                                    </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-8 py-5 border-b border-gray-50 bg-[#FDFDFD] flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                        <span class="w-2 h-4 bg-red-500 rounded-full"></span>
                                        Ingreso de Inventario
                                    </h3>
                                </div>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest border border-gray-200 px-2 py-1 rounded">Manual Entry</span>
                            </div>
                            <div ref="formularioSeccion" class="p-8">
                                <CreateProductForm :prefillData="iaFormSuggestions" :categories="categories" />
                            </div>
                        </div>

                        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                            <div class="flex justify-between items-center mb-8">
                                <div>
                                    <h3 class="font-bold text-slate-800 text-lg tracking-tight">Análisis de Tendencia</h3>
                                    <p class="text-gray-400 text-[11px] font-medium uppercase tracking-wider">Flujo de ventas histórico</p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="w-3 h-3 bg-red-500 rounded-full opacity-20 animate-pulse"></span>
                                    <span class="text-[10px] bg-slate-100 text-slate-600 px-3 py-1 rounded-lg font-bold">LIVE DATA</span>
                                </div>
                            </div>
                            <div v-if="isLoading" class="h-72 flex flex-col items-center justify-center gap-3">
                                <div class="w-10 h-10 border-4 border-red-500/20 border-t-red-500 rounded-full animate-spin"></div>
                                <span class="text-[10px] text-gray-400 font-bold uppercase animate-pulse">Consultando Red Neuronal...</span>
                            </div>
                            <div v-else class="h-72">
                                <SalesChart v-if="stats.chart_data?.values?.length > 0" :data="stats.chart_data.values" :labels="stats.chart_data.labels" />
                                <div v-else class="h-full flex flex-col items-center justify-center text-gray-400 bg-slate-50 rounded-2xl border-2 border-dashed border-gray-100">
                                    <span class="text-3xl mb-2">📊</span>
                                    <p class="text-xs font-bold uppercase tracking-widest text-gray-300">Esperando datos de mercado</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="bg-slate-900 rounded-3xl p-1 shadow-2xl shadow-red-900/10">
                            <div class="bg-white p-7 rounded-[calc(1.5rem-2px)] border border-slate-100">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 bg-red-50 rounded-lg text-red-600 text-xl font-bold">ML</div>
                                    <h3 class="font-black text-slate-900 text-sm uppercase tracking-tight">Machine Learning</h3>
                                </div>
                                <p class="text-gray-500 text-xs mb-6 leading-relaxed">Entrena el algoritmo con nuevos parámetros para mejorar la predicción de stock.</p>
                                <Link :href="route('ai-training.index')" class="group flex items-center justify-center gap-2 w-full bg-slate-900 text-white py-3.5 rounded-2xl font-bold text-xs hover:bg-red-700 transition-all duration-300 shadow-lg shadow-slate-200 hover:shadow-red-200">
                                    GESTIONAR CEREBRO
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Sección de Eventos -->
                        <div class="bg-slate-900 rounded-3xl p-1 shadow-2xl shadow-red-900/10">
                            <div class="bg-white p-7 rounded-[calc(1.5rem-2px)] border border-slate-100">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 bg-red-50 rounded-lg text-red-600 text-xl font-bold">EV</div>
                                    <h3 class="font-black text-slate-900 text-sm uppercase tracking-tight">Eventos</h3>
                                </div>
                                <p class="text-gray-500 text-xs mb-6 leading-relaxed">Gestiona noticias y actividades programadas para tu comunidad.</p>
                                <Link href="/admin/events" class="group flex items-center justify-center gap-2 w-full bg-slate-900 text-white py-3.5 rounded-2xl font-bold text-xs hover:bg-red-700 transition-all duration-300 shadow-lg shadow-slate-200 hover:shadow-red-200">
                                    GESTIONAR EVENTOS
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </Link>
                            </div>
                        </div>

                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-50 bg-[#FDFDFD] flex justify-between items-center">
                                <h3 class="font-bold text-slate-800 text-xs uppercase tracking-widest flex items-center gap-2">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                    </span>
                                    Recientes
                                </h3>
                                <span class="text-[9px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-bold">AUTO-UPDATE</span>
                            </div>
                            <div class="p-4">
                                <div v-if="productosConLabels.length > 0" class="space-y-2">
                                    <Link v-for="product in productosConLabels" 
                                          :key="product.id" 
                                          :href="`/product/${product.id}`"
                                          class="flex items-center p-3 hover:bg-slate-50 rounded-2xl transition-all group border border-transparent hover:border-slate-100">
                                        <div class="relative">
                                            <img v-if="product.images?.length" :src="`/storage/${product.images[0].image_path}`" class="w-11 h-11 object-cover rounded-xl shadow-sm">
                                            <div v-else class="w-11 h-11 bg-slate-100 rounded-xl flex items-center justify-center text-lg">📦</div>
                                            <div v-if="product.stock < 5" class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full"></div>
                                        </div>
                                        <div class="ml-4 flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <p class="text-sm font-bold text-slate-900 truncate group-hover:text-red-600 transition-colors">{{ product.name }}</p>
                                                <!-- Smart Label -->
                                                <span v-if="product.label" :class="[
                                                    'text-[9px] px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter',
                                                    'bg-red-100 text-red-700'
                                                ]">
                                                    {{ product.label }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span :class="['text-[10px] font-bold px-1.5 py-0.5 rounded', product.stock < 5 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500']">
                                                    STOCK: {{ product.stock }}
                                                </span>
                                                <span class="text-[10px] text-red-600 font-bold">S/ {{ product.price }}</span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                                <div v-else class="text-center py-10">
                                    <p class="text-[10px] text-gray-300 font-black uppercase tracking-widest">Sin registros activos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SalesChart from '@/Components/SalesChart.vue';
import CreateProductForm from '@/Pages/Admin/CreateProduct.vue';
import { ref, onMounted, computed } from 'vue';
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
    events: { type: Array, default: () => [] },
    lowStockCount: { type: Number, default: 0 },
    categories: { type: Array, default: () => [] }
});

// Arreglar ReferenceError chartCanvas
const chartCanvas = ref(null);

// Cerebro lógico avanzado - análisis IA con 3 escenarios dinámicos
const analisisIA = computed(() => {
    // Escenario 1: Crisis de Ventas vs Población Alta
    // Si las ventas son menores a 500 y hay muchos animales
    if (props.sales < 500 && props.totalPigs > 10) {
        return {
            clase: 'border-red-200 bg-red-50/50',
            icon: '✅',
            colorText: 'text-red-800',
            titulo: 'ALERTA DE SOBREPRODUCCIÓN',
            mensaje: `David, las ventas están lentas (S/ ${props.sales}) mientras la población crece. Cada día que pasa, el costo de forraje reduce tu ganancia neta.`,
            recomendacion: 'Activa un descuento del 15% en cuyes adultos para liberar espacio y recuperar liquidez.',
            btnClase: 'bg-red-700 hover:bg-red-800',
            btnText: '🔥 Lanzar Oferta Relámpago',
            tipoAccion: 'oferta'
        };
    }

    // Escenario 2: Stock Crítico (Urgencia de Inventario)
    if (props.lowStockCount > 0) {
        return {
            clase: 'border-rose-200 bg-rose-50/50',
            icon: '✅',
            colorText: 'text-rose-800',
            titulo: 'CRISIS DE INVENTARIO',
            mensaje: `Detecto ${props.lowStockCount} insumos agotados. Sin estos productos, el flujo de ventas de Mundo Yacus se detendrá por completo mañana.`,
            recomendacion: 'Realiza el pedido a proveedores ahora mismo para evitar quiebre de stock.',
            btnClase: 'bg-rose-600 hover:bg-rose-700',
            btnText: '📦 Reponer Inventario',
            tipoAccion: 'inventario'
        };
    }

    // Escenario 3: Todo marcha bien (Optimización)
    return {
        clase: 'border-red-200 bg-red-50/50',
        icon: '✅',
        colorText: 'text-red-800',
        titulo: 'ESTADO ÓPTIMO',
        mensaje: 'La relación población/ventas es excelente. No hay incendios que apagar en la granja.',
        recomendacion: 'Es el momento ideal para invertir en publicidad o mejorar las fotos de tus productos premium.',
        btnClase: 'bg-slate-900 hover:bg-red-700',
        btnText: ' Escalar Ventas',
        tipoAccion: 'escalar'
    };
});

// Smart Labels para productos recientes
const productosConLabels = computed(() => {
    return props.latestProducts.map(producto => {
        let label = null;
        let color = '';
        
        if (producto.stock < 3) {
            label = 'ÚLTIMOS';
            color = 'rose';
        } else if (producto.created_at) {
            const diasDesdeCreacion = Math.floor((new Date() - new Date(producto.created_at)) / (1000 * 60 * 60 * 24));
            if (diasDesdeCreacion <= 7) {
                label = 'NUEVO';
                color = 'emerald';
            }
        }
        
        return {
            ...producto,
            label,
            color
        };
    });
});

// Cálculo inteligente de Proyección Stock
const stockSugerido = computed(() => {
    const ventas = props.sales || 0;
    const poblacion = props.totalPigs || 0;
    const stockBajo = props.lowStockCount || 0;
    
    // Si hay crisis de stock, sugerir reponer
    if (stockBajo > 0) {
        return Math.ceil(poblacion * 1.3); // Aumentar 30%
    }
    
    // Si ventas altas y población baja, reducir stock
    if (ventas > 500 && poblacion < 15) {
        return Math.ceil(poblacion * 0.7); // Reducir 30%
    }
    
    // Si todo está bien, mantener equilibrio
    return Math.ceil(poblacion * 1.0); // Mantener stock actual
});

// Formato de moneda peruana para ingresos
const ingresosFormateados = computed(() => {
    return `S/ ${props.sales.toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
});

// Referencias y datos para la IA
const formularioSeccion = ref(null);
const iaFormSuggestions = ref({}); // Datos que enviaremos al formulario

// Función para ejecutar acciones de la IA
const ejecutarAccionIA = (tipo) => {
    // 1. Definir qué vamos a autocompletar según la sugerencia
    if (tipo === 'oferta') {
        iaFormSuggestions.value = {
            name: 'Cuy Raza Perú - Selección Especial',
            price: 45.00,
            description: 'Ejemplar de alta calidad genética, criado con forraje natural. Ideal para reproducción o consumo. ¡Oferta por tiempo limitado para optimizar stock!'
        };
    } else if (tipo === 'inventario') {
        iaFormSuggestions.value = {
            name: 'Forraje Premium - Mezcla Especial',
            price: 25.00,
            description: 'Mezcla nutritiva especial para cuyes de alta producción. Formula balanceada con vitaminas esenciales para crecimiento óptimo. Producto esencial para mantener el flujo de ventas activo.'
        };
    } else if (tipo === 'escalar') {
        iaFormSuggestions.value = {
            name: 'Cuy Premium - Línea Especial Huánuco',
            price: 85.00,
            description: 'Ejemplar selecto de línea premium, criado con técnicas avanzadas. Genética superior garantizada para máxima rentabilidad. Producto exclusivo para escalar las ventas actuales.'
        };
    }

    // 2. Hacer scroll suave hasta el formulario para que David empiece a trabajar
    formularioSeccion.value?.scrollIntoView({ behavior: 'smooth' });
    
    // Aquí podrías disparar una notificación pequeña
    console.log("IA: Formulario pre-configurado para " + tipo);
};

const ejecutarIA = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post('/api/ai/analyze');
        
        if (response.data && response.data.stats) {
            // Actualizar las estadísticas con los datos de la IA
            stats.value = { ...stats.value, ...response.data.stats };
        }
    } catch (error) {
        console.error('Error al ejecutar análisis IA:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    if (props.stats && props.stats.chart_data) {
        setTimeout(() => {
            if (chartCanvas.value) {
                const ctx = chartCanvas.value.getContext('2d');
                if (!ctx) {
                    console.warn('SalesChart: No se puede obtener el contexto del canvas');
                    return;
                }

                // Crear gradiente de relleno
                const gradient = ctx.createLinearGradient(0, 0, 0, 320);
                gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');  // Esmeralda suave
                gradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)'); // Transparente

                chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: props.stats.chart_data.labels,
                        datasets: [{
                            label: 'Ventas de Mundo Yacus',
                            data: props.stats.chart_data.values,
                            borderColor: '#10b981',           // Esmeralda moderno
                            backgroundColor: gradient,        // Gradiente de relleno
                            borderWidth: 3,                   // Grosor de línea
                            tension: 0.4,                    // Línea curva suave
                            fill: true,                       // Relleno con gradiente
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 0,                   // Puntos ocultos por defecto
                            pointHoverRadius: 6,             // Puntos visibles en hover
                            pointHoverBackgroundColor: '#10b981',
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1500,                  // Animación suave de 1.5s
                            easing: 'easeInOutQuart'
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: { 
                                display: false 
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: '#10b981',
                                borderWidth: 1,
                                padding: 12,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return 'Ventas: S/ ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                suggestedMax: 15,
                                ticks: {
                                    stepSize: 1,
                                    color: '#6b7280',
                                    font: {
                                        size: 11,
                                        weight: '500'
                                    },
                                    callback: function(value) {
                                        return 'S/ ' + value;  // Símbolo de Soles
                                    }
                                },
                                grid: { 
                                    color: 'rgba(229, 231, 235, 0.3)',  // Grid muy tenue
                                    drawBorder: false
                                },
                                border: {
                                    display: false
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#6b7280',
                                    font: {
                                        size: 10,
                                        weight: '500'
                                    },
                                    maxRotation: 45,
                                    minRotation: 45,
                                    autoSkip: true,
                                    maxTicksLimit: 15,  // Mostrar máximo 15 etiquetas para no saturar
                                    callback: function(value, index) {
                                        // Mostrar etiqueta cada 2 días para 30 días
                                        return index % 2 === 0 ? this.getLabelForValue(value) : '';
                                    }
                                },
                                grid: { 
                                    display: false,
                                    drawBorder: false
                                },
                                border: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        }, 100);
    }
});
</script>
