<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    events: Array,
    banner_text: String,
    banner_active: Boolean
});

const form = useForm({
    title: '',
    description: '',
    event_date: '',
    image: null,
});

const bannerForm = useForm({
    banner_text: props.banner_text || '',
    banner_active: props.banner_active || false,
});

const submit = () => {
    form.post(route('admin.events.store'), {
        onSuccess: () => form.reset(),
    });
};

const saveBanner = () => {
    bannerForm.post(route('admin.events.settings.update'));
};

const deleteEvent = (event) => {
    if (confirm('¿Estás seguro de eliminar este evento?')) {
        useForm().delete(route('admin.events.destroy', event.id));
    }
};

const handleImageUpload = (e) => {
    form.image = e.target.files[0];
};
</script>

<template>
    <Head title="Gestión de Eventos" />

    <div class="py-12 bg-slate-100/50 min-h-screen">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
            
            <!-- Header Admin -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 p-8 rounded-3xl shadow-2xl border-b-4 border-emerald-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-emerald-500/20">
                        Comunicaciones
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Gestión de Eventos
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Noticias y comunicados de la comunidad.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-emerald-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total</span>
                    <p class="text-2xl font-black text-emerald-400 leading-none">{{ events?.length || 0 }} <span class="text-xs text-emerald-600">eventos</span></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Formulario Principal -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Banner Global -->
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                                <span class="text-2xl">📢</span> Aviso Global
                            </h3>
                            <p class="text-slate-500 text-xs mt-1">Mensaje en el encabezado del sitio</p>
                        </div>
                        <div class="p-8 space-y-6">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-black text-slate-700 uppercase tracking-widest">¿Activar en cabezal?</label>
                                <div class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200">
                                    <input type="checkbox" v-model="bannerForm.banner_active" class="sr-only peer">
                                    <div @click="bannerForm.banner_active = !bannerForm.banner_active" 
                                         :class="bannerForm.banner_active ? 'bg-emerald-500' : 'bg-gray-300'"
                                         class="w-11 h-6 rounded-full relative cursor-pointer">
                                        <div :class="bannerForm.banner_active ? 'translate-x-6' : 'translate-x-1'"
                                             class="absolute top-1 h-4 w-4 rounded-full bg-white transition-transform"></div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">Mensaje del Banner</label>
                                <textarea 
                                    v-model="bannerForm.banner_text" 
                                    rows="3"
                                    class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                    placeholder="Ej: ¡Atención! Por feriado local no habrá atención este domingo."
                                ></textarea>
                            </div>

                            <button @click="saveBanner" 
                                    :disabled="bannerForm.processing"
                                    class="w-full bg-emerald-500 text-slate-900 py-3 px-6 rounded-xl font-black hover:bg-slate-900 hover:text-emerald-400 transition-all shadow-lg disabled:opacity-50">
                                <span v-if="bannerForm.processing">Guardando...</span>
                                <span v-else>Actualizar Aviso</span>
                            </button>
                        </div>
                    </div>

                    <!-- Nuevo Evento -->
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                                <span class="text-2xl">📝</span> Crear Nuevo Evento
                            </h3>
                            <p class="text-slate-500 text-xs mt-1">Añadir noticias y comunicados</p>
                        </div>
                        <div class="p-8">
                            <form @submit.prevent="submit" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">Título del Evento</label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                        placeholder="Ej: Gran Feria Gastronómica"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">Fecha del Evento</label>
                                    <input
                                        v-model="form.event_date"
                                        type="date"
                                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">Descripción</label>
                                    <textarea
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                        placeholder="Describe los detalles del evento..."
                                        required
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3">Imagen del Evento</label>
                                    <input
                                        type="file"
                                        @change="handleImageUpload"
                                        accept="image/*"
                                        class="w-full text-sm text-slate-500 border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-black file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full bg-emerald-500 text-slate-900 py-3 px-6 rounded-xl font-black hover:bg-slate-900 hover:text-emerald-400 transition-all shadow-lg disabled:opacity-50"
                                >
                                    <span v-if="form.processing">Creando...</span>
                                    <span v-else>Crear Evento</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Lista de Eventos -->
                <div class="space-y-8">
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-500 border border-slate-200 overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-black text-slate-800 flex items-center gap-3 text-lg">
                                <span class="text-2xl">📋</span> Eventos Activos ({{ events.length }})
                            </h3>
                            <p class="text-slate-500 text-xs mt-1">Historial de comunicados</p>
                        </div>
                        <div class="p-8">
                            <div class="space-y-4">
                                <div v-for="event in events" :key="event.id" 
                                     class="border border-slate-200 rounded-2xl p-6 hover:bg-slate-50 transition-all group">
                                    
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-black text-slate-900 group-hover:text-emerald-600 transition-colors">{{ event.title }}</h3>
                                            <p class="text-sm text-slate-600 mt-2">{{ event.description }}</p>
                                            <p class="text-xs text-slate-500 mt-3">
                                                📅 {{ event.formatted_date || event.event_date }}
                                            </p>
                                        </div>
                                        
                                        <div class="flex items-center gap-3">
                                            <span :class="event.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800'" 
                                                  class="px-3 py-1 text-xs font-black uppercase tracking-widest rounded-full">
                                                {{ event.is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                            
                                            <button
                                                @click="deleteEvent(event)"
                                                class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-xl transition-all"
                                                title="Eliminar evento"
                                            >
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="events.length === 0" class="text-center py-12">
                                <div class="text-6xl mb-4">📭</div>
                                <p class="text-slate-400 font-black uppercase tracking-widest">No hay eventos creados</p>
                                <p class="text-slate-500 text-xs mt-2">Crea tu primer evento usando el formulario</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>