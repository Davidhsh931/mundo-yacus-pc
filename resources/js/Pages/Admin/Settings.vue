<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    settings: Array,
    success: String
});

// Organizar configuraciones por grupos
const businessSettings = computed(() => {
    return props.settings.filter(s => ['business_name', 'currency_symbol'].includes(s.key));
});

const paymentSettings = computed(() => {
    return props.settings.filter(s => ['whatsapp_number', 'yape_number', 'plin_number', 'enable_whatsapp'].includes(s.key));
});

// Formulario para actualizar configuraciones
const form = useForm({
    whatsapp_number: props.settings.find(s => s.key === 'whatsapp_number')?.value || '51987654321',
    business_name: props.settings.find(s => s.key === 'business_name')?.value || 'Mundo Yacus',
    yape_number: props.settings.find(s => s.key === 'yape_number')?.value || '987654321',
    plin_number: props.settings.find(s => s.key === 'plin_number')?.value || '987654321',
    currency_symbol: props.settings.find(s => s.key === 'currency_symbol')?.value || 'S/',
    enable_whatsapp: props.settings.find(s => s.key === 'enable_whatsapp')?.value === '1',
});

const submit = () => {
    form.put(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Recargar la página para mostrar los nuevos valores
            window.location.reload();
        }
    });
};
</script>

<template>
    <Head title="Configuraciones - Mundo Yacus" />
    <AdminLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 p-8 rounded-3xl shadow-2xl border-b-4 border-emerald-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-emerald-500/20">
                        Sistema
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Configuraciones
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Ajustes generales de la plataforma.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-emerald-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Parámetros</span>
                    <p class="text-2xl font-black text-emerald-400 leading-none">{{ settings?.length || 0 }} <span class="text-xs text-emerald-600">configuraciones</span></p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">

                <!-- Mensaje de éxito -->
                <div v-if="success" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 shadow-lg">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">✅</span>
                        <p class="text-emerald-800 font-black text-sm">{{ success }}</p>
                    </div>
                </div>

                <!-- Formulario de configuraciones -->
                <form @submit.prevent="submit" class="space-y-12">
                    <!-- Configuraciones del Negocio -->
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                                <span class="text-2xl">🏪</span> Información del Negocio
                            </h3>
                            <p class="text-slate-500 text-xs mt-1">Datos básicos de tu empresa</p>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">
                                        Nombre del Negocio
                                    </label>
                                    <input 
                                        v-model="form.business_name"
                                        type="text" 
                                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                        placeholder="Mundo Yacus"
                                    >
                                    <p class="mt-2 text-xs text-slate-500">Nombre que aparecerá en la aplicación</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">
                                        Símbolo de Moneda
                                    </label>
                                    <input 
                                        v-model="form.currency_symbol"
                                        type="text" 
                                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                        placeholder="S/"
                                        maxlength="5"
                                    >
                                <p class="mt-2 text-xs text-slate-500">Símbolo para mostrar precios</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuraciones de Pago -->
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                                <span class="text-2xl">💳</span> Métodos de Pago y Contacto
                            </h3>
                            <p class="text-slate-500 text-xs mt-1">Configuración de medios de pago</p>
                        </div>
                        <div class="p-8 space-y-8">
                            <!-- WhatsApp -->
                            <div>
                                <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">
                                    📱 Número de WhatsApp para Pagos
                                </label>
                                <input 
                                    v-model="form.whatsapp_number"
                                    type="text" 
                                    class="w-full md:max-w-lg border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                    placeholder="51987654321"
                                >
                                <p class="mt-2 text-xs text-slate-500">
                                    ⚠️ Importante: Este número se usará en todos los botones de WhatsApp de la aplicación
                                </p>
                            </div>

                            <!-- Yape y Plin -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">
                                        💜 Número de Yape
                                    </label>
                                    <input 
                                        v-model="form.yape_number"
                                        type="text" 
                                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                        placeholder="987654321"
                                    >
                                    <p class="mt-2 text-xs text-slate-500">Número para pagos Yape</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">
                                        💙 Número de Plin
                                    </label>
                                    <input 
                                        v-model="form.plin_number"
                                        type="text" 
                                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                        placeholder="987654321"
                                    >
                                    <p class="mt-2 text-xs text-slate-500">Número para pagos Plin</p>
                                </div>
                            </div>

                            <!-- Habilitar WhatsApp -->
                            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl">
                                <input 
                                    type="checkbox" 
                                    v-model="form.enable_whatsapp"
                                    id="enable_whatsapp"
                                    class="w-5 h-5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500"
                                >
                                <label for="enable_whatsapp" class="flex-1">
                                    <span class="text-sm font-black text-slate-700">Habilitar botones de WhatsApp</span>
                                    <p class="text-xs text-slate-500">Muestra los botones de contacto en toda la aplicación</p>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex justify-end gap-4">
                        <Link 
                            href="/admin/dashboard" 
                            class="px-8 py-3 border border-slate-300 text-slate-700 rounded-xl font-black hover:bg-slate-50 transition-all"
                        >
                            Cancelar
                        </Link>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-8 py-3 bg-emerald-500 text-slate-900 rounded-xl font-black hover:bg-slate-900 hover:text-emerald-400 transition-all shadow-lg disabled:opacity-50"
                        >
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar Cambios</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
                        
