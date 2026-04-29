<script setup>
import { ref } from 'vue';

const isRunning = ref(false);
const testResults = ref(null);
const lastReport = ref(null);

const runQuickTest = async () => {
    isRunning.value = true;
    try {
        const response = await fetch('/chat-test/quick');
        const data = await response.json();
        testResults.value = data;
    } catch (error) {
        console.error('Error running quick test:', error);
        testResults.value = { error: error.message };
    } finally {
        isRunning.value = false;
    }
};

const runFullTest = async () => {
    isRunning.value = true;
    try {
        const response = await fetch('/chat-test/run');
        const data = await response.json();
        testResults.value = data;
    } catch (error) {
        console.error('Error running full test:', error);
        testResults.value = { error: error.message };
    } finally {
        isRunning.value = false;
    }
};

const getLastReport = async () => {
    try {
        const response = await fetch('/chat-test/report');
        if (response.ok) {
            const data = await response.json();
            lastReport.value = data;
        }
    } catch (error) {
        console.error('Error getting last report:', error);
    }
};

// Auto-cargar último reporte
getLastReport();
</script>

<template>
    <div class="fixed bottom-20 right-6 z-50 bg-white rounded-lg shadow-xl p-4 w-96 max-h-96 overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">🧪 Chat Tests</h3>
            <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                ✕
            </button>
        </div>

        <!-- Botones de prueba -->
        <div class="flex gap-2 mb-4">
            <button
                @click="runQuickTest"
                :disabled="isRunning"
                class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50 text-sm"
            >
                {{ isRunning ? '⏳' : '⚡' }} Quick Test
            </button>
            <button
                @click="runFullTest"
                :disabled="isRunning"
                class="px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50 text-sm"
            >
                {{ isRunning ? '⏳' : '🔬' }} Full Test
            </button>
            <button
                @click="getLastReport"
                class="px-3 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 text-sm"
            >
                📊 Report
            </button>
        </div>

        <!-- Resultados del test -->
        <div v-if="testResults" class="space-y-2">
            <div v-if="testResults.error" class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded">
                ❌ Error: {{ testResults.error }}
            </div>
            
            <div v-else>
                <!-- Resumen -->
                <div class="bg-gray-100 rounded p-3">
                    <div class="flex justify-between text-sm">
                        <span>Total: {{ testResults.total }}</span>
                        <span>✅ Passed: {{ testResults.passed }}</span>
                        <span>❌ Failed: {{ testResults.failed }}</span>
                    </div>
                    <div class="mt-1">
                        <div class="w-full bg-gray-300 rounded-full h-2">
                            <div 
                                class="bg-green-500 h-2 rounded-full transition-all"
                                :style="{ width: testResults.pass_rate + '%' }"
                            ></div>
                        </div>
                        <div class="text-xs text-center mt-1">{{ testResults.pass_rate }}%</div>
                    </div>
                </div>

                <!-- Resultados individuales -->
                <div class="space-y-1 max-h-48 overflow-y-auto">
                    <div 
                        v-for="(result, index) in testResults.results" 
                        :key="index"
                        class="text-xs p-2 rounded"
                        :class="result.passed ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'"
                    >
                        <div class="flex justify-between">
                            <span class="font-medium">{{ result.message }}</span>
                            <span>{{ result.passed ? '✅' : '❌' }}</span>
                        </div>
                        <div v-if="!result.passed" class="text-red-600 mt-1">
                            Response: {{ result.response.substring(0, 50) }}...
                        </div>
                        <div class="text-gray-500 mt-1">
                            Category: {{ result.category }} | Time: {{ result.response_time_ms }}ms
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Último reporte -->
        <div v-if="lastReport && !testResults" class="space-y-2">
            <div class="bg-purple-50 border border-purple-200 rounded p-3">
                <h4 class="font-medium text-purple-800">📊 Last Report</h4>
                <div class="text-sm mt-2">
                    <div>📅 {{ new Date(lastReport.timestamp).toLocaleString() }}</div>
                    <div>✅ {{ lastReport.summary.passed }}/{{ lastReport.summary.total }} passed</div>
                    <div>📈 {{ lastReport.summary.pass_rate }}% success rate</div>
                    <div>⏱️ {{ lastReport.summary.avg_response_time }}ms avg time</div>
                </div>
            </div>
        </div>

        <!-- Recomendaciones -->
        <div v-if="testResults && testResults.recommendations" class="space-y-1">
            <div 
                v-for="(rec, index) in testResults.recommendations" 
                :key="index"
                class="bg-yellow-50 border border-yellow-200 rounded p-2 text-xs"
            >
                💡 {{ rec }}
            </div>
        </div>
    </div>
</template>
