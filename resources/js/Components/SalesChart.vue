<template>
    <div class="h-72">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const chartCanvas = ref(null);
let chartInstance = null;

onMounted(() => {
    // Datos de prueba directamente en el componente
    const testData = [12, 19, 15, 25, 22, 30, 28, 35, 32, 38, 42, 45, 48, 52, 55, 58, 62, 65, 68, 70, 72, 75, 78, 80, 82, 85, 87, 90, 92, 95];
    const testLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];

    console.log('Iniciando gráfico con datos:', testData);

    if (!chartCanvas.value) {
        console.error('Canvas no encontrado');
        return;
    }

    const ctx = chartCanvas.value.getContext('2d');
    if (!ctx) {
        console.error('No se puede obtener el contexto del canvas');
        return;
    }

    // Crear gradiente de relleno
    const gradient = ctx.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');  // Esmeralda suave
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)'); // Transparente

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: testLabels,
            datasets: [{
                label: 'Ventas de Mundo Yacus',
                data: testData,
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
                    suggestedMax: 100,
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

    console.log('Gráfico inicializado correctamente');
});
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
