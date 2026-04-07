<script setup>
import { onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    data: { type: Array, default: () => [] },
    labels: { type: Array, default: () => [] }
});

const chartCanvas = ref(null);
let chartInstance = null;

const initChart = () => {
    // Verificar que el canvas exista
    if (!chartCanvas.value) {
        console.warn('SalesChart: Canvas no encontrado');
        return;
    }

    // Evitamos duplicados destruyendo la instancia previa
    if (chartInstance) {
        chartInstance.destroy();
    }

    const ctx = chartCanvas.value.getContext('2d');
    if (!ctx) {
        console.warn('SalesChart: No se puede obtener el contexto del canvas');
        return;
    }

    // Crear gradiente de relleno
    const gradient = ctx.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(139, 92, 246, 0.3)');  // Púrpura suave
    gradient.addColorStop(1, 'rgba(139, 92, 246, 0.01)'); // Transparente

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.labels,
            datasets: [{
                label: 'Ventas de Mundo Yacus',
                data: props.data,
                borderColor: '#8b5cf6',           // Púrpura moderno
                backgroundColor: gradient,        // Gradiente de relleno
                borderWidth: 3,                   // Grosor de línea
                tension: 0.4,                    // Línea curva suave
                fill: true,                       // Relleno con gradiente
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 0,                   // Puntos ocultos por defecto
                pointHoverRadius: 6,             // Puntos visibles en hover
                pointHoverBackgroundColor: '#8b5cf6',
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
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#8b5cf6',
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
};

onMounted(() => {
    // Esperar a que el DOM esté completamente renderizado
    setTimeout(initChart, 100);
});

// Vigilamos cambios profundos en los datos
watch(() => [props.data, props.labels], (newVal) => {
    console.log('Datos recibidos en SalesChart:', {
        data: newVal[0],
        labels: newVal[1]
    });
    
    if (newVal[0] && newVal[0].length > 0 && newVal[1] && newVal[1].length > 0) {
        setTimeout(initChart, 50); // Pequeña espera para asegurar renderizado
    }
}, { deep: true, immediate: true });
</script>

<template>
    <div class="w-full bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                📈 Tendencia de Ventas (Datos de la Chacra)
            </h3>
        </div>
        <div style="position: relative; height: 320px;">
            <canvas ref="chartCanvas"></canvas>
        </div>
    </div>
</template>