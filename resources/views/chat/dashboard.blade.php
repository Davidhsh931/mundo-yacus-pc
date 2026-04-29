<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytics - Chat Mundo Yacus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100" x-data="dashboard()">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">📊 Chat Analytics</h1>
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Tiempo Real</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <select x-model="timeRange" @change="loadData()" class="border rounded px-3 py-1 text-sm">
                            <option value="today">Hoy</option>
                            <option value="week">Esta Semana</option>
                            <option value="month">Este Mes</option>
                        </select>
                        <button @click="refreshData()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                            🔄 Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total Mensajes</p>
                            <p class="text-2xl font-bold text-gray-900" x-text="stats.totalMessages">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Tasa Conversión</p>
                            <p class="text-2xl font-bold text-gray-900"><span x-text="stats.conversionRate">0</span>%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Tiempo Promedio</p>
                            <p class="text-2xl font-bold text-gray-900"><span x-text="stats.avgResponseTime">0</span>s</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Satisfacción</p>
                            <p class="text-2xl font-bold text-gray-900"><span x-text="stats.satisfaction">0</span>/5</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Messages Over Time -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Mensajes en el Tiempo</h3>
                    <canvas id="messagesChart" width="400" height="200"></canvas>
                </div>

                <!-- Button Clicks -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🎯 Clics en Botones</h3>
                    <canvas id="buttonsChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Advanced Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Top Intents -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🎯 Intenciones Principales</h3>
                    <div class="space-y-2">
                        <template x-for="intent in topIntents" :key="intent.name">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600" x-text="intent.name"></span>
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-500 h-2 rounded-full" :style="`width: ${intent.percentage}%`"></div>
                                    </div>
                                    <span class="text-sm font-medium" x-text="intent.count"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Popular Products -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🐹 Productos Populares</h3>
                    <div class="space-y-2">
                        <template x-for="product in popularProducts" :key="product.name">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600" x-text="product.name"></span>
                                <span class="text-sm font-medium text-green-600" x-text="product.mentions + ' menciones'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- User Activity -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">👥 Actividad de Usuarios</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Usuarios Activos</span>
                            <span class="text-sm font-medium" x-text="stats.activeUsers"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Nuevos Hoy</span>
                            <span class="text-sm font-medium text-green-600" x-text="stats.newUsers"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Tasa Retención</span>
                            <span class="text-sm font-medium text-blue-600" x-text="stats.retentionRate + '%'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Real-time Activity Feed -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🔥 Actividad en Tiempo Real</h3>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="activity in realTimeActivity" :key="activity.id">
                        <div class="flex items-center space-x-2 text-sm">
                            <span class="text-gray-500" x-text="activity.time"></span>
                            <span class="font-medium" x-text="activity.user"></span>
                            <span class="text-gray-600" x-text="activity.action"></span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs" x-text="activity.type"></span>
                        </div>
                    </template>
                </div>
            </div>
        </main>
    </div>

    <script>
        function dashboard() {
            return {
                timeRange: 'today',
                stats: {
                    totalMessages: 0,
                    conversionRate: 0,
                    avgResponseTime: 0,
                    satisfaction: 0,
                    activeUsers: 0,
                    newUsers: 0,
                    retentionRate: 0
                },
                topIntents: [],
                popularProducts: [],
                realTimeActivity: [],
                charts: {},

                init() {
                    this.loadData();
                    this.initCharts();
                    this.startRealTimeUpdates();
                },

                async loadData() {
                    try {
                        const response = await fetch(`/api/chat/analytics?range=${this.timeRange}`);
                        const data = await response.json();
                        
                        this.updateStats(data.stats);
                        this.updateCharts(data.charts);
                        this.updateTopIntents(data.intents);
                        this.updatePopularProducts(data.products);
                    } catch (error) {
                        console.error('Error loading analytics:', error);
                    }
                },

                updateStats(stats) {
                    this.stats = { ...this.stats, ...stats };
                },

                initCharts() {
                    // Messages Chart
                    const messagesCtx = document.getElementById('messagesChart').getContext('2d');
                    this.charts.messages = new Chart(messagesCtx, {
                        type: 'line',
                        data: {
                            labels: [],
                            datasets: [{
                                label: 'Mensajes',
                                data: [],
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });

                    // Buttons Chart
                    const buttonsCtx = document.getElementById('buttonsChart').getContext('2d');
                    this.charts.buttons = new Chart(buttonsCtx, {
                        type: 'doughnut',
                        data: {
                            labels: [],
                            datasets: [{
                                data: [],
                                backgroundColor: [
                                    '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                },

                updateCharts(chartData) {
                    if (chartData.messages) {
                        this.charts.messages.data.labels = chartData.messages.labels;
                        this.charts.messages.data.datasets[0].data = chartData.messages.data;
                        this.charts.messages.update();
                    }

                    if (chartData.buttons) {
                        this.charts.buttons.data.labels = chartData.buttons.labels;
                        this.charts.buttons.data.datasets[0].data = chartData.buttons.data;
                        this.charts.buttons.update();
                    }
                },

                updateTopIntents(intents) {
                    this.topIntents = intents.map(intent => ({
                        ...intent,
                        percentage: Math.round((intent.count / intents.reduce((sum, i) => sum + i.count, 0)) * 100)
                    }));
                },

                updatePopularProducts(products) {
                    this.popularProducts = products;
                },

                startRealTimeUpdates() {
                    setInterval(async () => {
                        try {
                            const response = await fetch('/api/chat/analytics/realtime');
                            const activities = await response.json();
                            
                            this.realTimeActivity = activities.slice(0, 10);
                        } catch (error) {
                            console.error('Error loading real-time data:', error);
                        }
                    }, 5000);

                    // Simulated initial data
                    this.realTimeActivity = [
                        { id: 1, time: '14:32', user: 'Juan Pérez', action: 'preguntó por precios', type: 'consulta' },
                        { id: 2, time: '14:31', user: 'María García', action: 'hizo clic en "Comprar"', type: 'conversión' },
                        { id: 3, time: '14:30', user: 'Carlos López', action: 'usó comando de voz', type: 'interacción' }
                    ];
                },

                refreshData() {
                    this.loadData();
                }
            }
        }
    </script>
</body>
</html>
