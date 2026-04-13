<template>
    <div class="h-72 relative">
        <!-- Loading state -->
        <div v-if="loading" class="absolute inset-0 flex items-center justify-center">
            <span class="text-gray-400 text-sm">Cargando datos de ventas...</span>
        </div>

        <!-- Error state -->
        <div v-else-if="error" class="absolute inset-0 flex items-center justify-center">
            <span class="text-red-400 text-sm">{{ error }}</span>
        </div>

        <!-- Empty state -->
        <div v-else-if="isEmpty" class="absolute inset-0 flex items-center justify-center">
            <span class="text-gray-400 text-sm">No hay ventas registradas en los últimos 30 días.</span>
        </div>

        <canvas ref="chartCanvas" :class="{ 'opacity-0': loading || error || isEmpty }"></canvas>
    </div>
</template>

<script setup>
import { onMounted, ref, nextTick } from 'vue';
import Chart from 'chart.js/auto';

const chartCanvas = ref(null);
const loading     = ref(true);
const error       = ref(null);
const isEmpty     = ref(false);
let chartInstance = null;

function buildChart(labels, data) {
    const ctx = chartCanvas.value.getContext('2d');

    // Crear gradiente de relleno
    const gradient = ctx.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');  // Esmeralda suave
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)'); // Transparente

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Ventas de Mundo Yacus',
                data,
                borderColor: '#10b981',           // Esmeralda moderno
                backgroundColor: gradient,        // Gradiente de relleno
                borderWidth: 3,                   // Grosor de línea
                tension: 0.4,                     // Línea curva suave
                fill: true,                       // Relleno con gradiente
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 0,                   // Puntos ocultos por defecto
                pointHoverRadius: 6,              // Puntos visibles en hover
                pointHoverBackgroundColor: '#10b981',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1500,                   // Animación suave de 1.5s
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
                            return 'Ventas: S/ ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 20,
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
                            // Show only the day number (e.g. "15") every other tick
                            if (index % 2 !== 0) return '';
                            const label = this.getLabelForValue(value); // "YYYY-MM-DD"
                            return label ? label.split('-')[2] : '';
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

onMounted(async () => {
    try {
        const response = await fetch('/api/sales/last-30-days');

        if (!response.ok) {
            throw new Error(`Error del servidor: ${response.status}`);
        }

        const { labels, data } = await response.json();

        const hasData = data.some(v => v > 0);
        if (!hasData) {
            isEmpty.value = true;
            loading.value = false;
            return;
        }

        loading.value = false;

        // Wait for Vue to render the canvas before Chart.js touches it
        await nextTick();

        if (!chartCanvas.value) {
            console.error('Canvas no encontrado tras nextTick');
            return;
        }

        buildChart(labels, data);

    } catch (err) {
        console.error('Error al cargar datos de ventas:', err);
        error.value = 'No se pudieron cargar los datos de ventas.';
        loading.value = false;
    }
});
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
