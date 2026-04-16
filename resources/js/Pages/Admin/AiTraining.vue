<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ categories: Array, trashed: Array });
const showNewCategoryForm = ref(false);

const newCategoryForm = useForm({
    name: '',
});

const createCategory = () => {
    newCategoryForm.post(route('ai-training.store'), {
        onSuccess: () => {
            newCategoryForm.reset();
            showNewCategoryForm.value = false;
        },
    });
};

const saveTraining = (category) => {
  const form = useForm({ 
    name: category.name,
    training_data: category.training_data 
  });
  form.post(route('ai-training.update', category.id), {
    preserveScroll: true,
    onSuccess: () => alert('🧠 ¡Categoría actualizada: ' + category.name + '!')
  });
};

const deleteCategory = (id) => {
    if (confirm('¿Enviar a la papelera? Los productos de esta categoría no se borrarán.')) {
        router.delete(route('ai-training.destroy', id));
    }
};

const restoreCategory = (id) => {
    router.post(route('ai-training.restore', id));
};

// Lógica de eliminación definitiva
const permanentDelete = (id) => {
    if (confirm('⚠️ ¿ESTÁS SEGURO? Esta acción no se puede deshacer y la categoría desaparecerá de la base de datos.')) {
        router.delete(route('ai-training.force-delete', id));
    }
};
</script>

<template>
    <AuthenticatedLayout :categories="categories">
        <Head title="IA Training" />
        
        <div class="max-w-7xl mx-auto p-6 sm:p-10 bg-slate-50 min-h-screen font-sans selection:bg-indigo-100 selection:text-indigo-700">
            
            <!-- Header Personalizado dentro del Layout -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-gradient-to-br from-indigo-600 to-violet-700 rounded-2xl text-white text-2xl shadow-xl shadow-indigo-200/50 rotate-3 group-hover:rotate-0 transition-transform">
                            <span class="block drop-shadow-md">🧠</span>
                        </div>
                        <div>
                            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-none">Neural Core</h1>
                            <p class="text-slate-500 text-sm font-medium mt-1">Entrenamiento de semántica para <span class="text-indigo-600 font-bold">Mundo Yacus</span></p>
                        </div>
                    </div>
                </div>
                
                <button 
                    @click="showNewCategoryForm = !showNewCategoryForm"
                    :class="showNewCategoryForm ? 'bg-white text-slate-600 border-slate-200 shadow-sm' : 'bg-slate-900 text-white shadow-xl shadow-slate-200 hover:bg-indigo-600'"
                    class="group px-6 py-3 rounded-2xl font-bold text-sm transition-all flex items-center gap-3 border-2 border-transparent active:scale-95"
                >
                    <template v-if="!showNewCategoryForm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70 group-hover:rotate-90 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Registrar Nodo
                    </template>
                    <template v-else>
                        <span class="text-lg">✕</span> Cerrar Panel
                    </template>
                </button>
            </header>

            <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                <div v-if="showNewCategoryForm" class="bg-white/80 backdrop-blur-md p-8 rounded-3xl border border-white shadow-[0_20px_50px_rgba(79,70,229,0.1)] mb-12 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-indigo-600"></div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">01</div>
                        <h3 class="font-black uppercase tracking-widest text-xs text-slate-400">Configuración de Nuevo Descriptor</h3>
                    </div>
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input 
                                v-model="newCategoryForm.name"
                                placeholder="Ej: Reproductores Machos Elite"
                                class="w-full px-6 py-4 bg-slate-100/50 border-transparent rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white focus:border-indigo-500 transition-all font-bold text-slate-700 placeholder-slate-400"
                                @keyup.enter="createCategory"
                            />
                        </div>
                        <button 
                            @click="createCategory"
                            :disabled="newCategoryForm.processing"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-4 rounded-2xl font-black shadow-lg shadow-indigo-200 transition-all active:scale-95 disabled:opacity-50 flex items-center gap-3"
                        >
                            DESPLEGAR NODO
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586L-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="newCategoryForm.errors.name" class="text-red-500 text-xs mt-4 font-bold flex items-center gap-2">
                        <span class="w-5 h-5 bg-red-100 rounded-full animate-pulse"></span> {{ newCategoryForm.errors.name }}
                    </p>
                </div>
            </transition>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div v-for="category in categories" :key="category.id" 
           class="group bg-white rounded-[2rem] shadow-sm border border-slate-200 hover:shadow-2xl hover:shadow-indigo-500/5 hover:border-indigo-200 transition-all duration-500 flex flex-col relative overflow-hidden">
        
        <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-100 transition-opacity">
             <span class="text-4xl font-black text-slate-100 group-hover:text-indigo-50">#{{ category.id }}</span>
        </div>

        <div class="p-8 pb-4 flex justify-between items-start relative z-10">
            <div class="flex-1 pr-10">
                <span class="text-[10px] font-black text-indigo-500 tracking-[0.3em] uppercase block mb-2 px-1">Clasificación Semántica</span>
                <input 
                    v-model="category.name" 
                    class="text-2xl font-black text-slate-800 border-b-2 border-transparent focus:border-indigo-500 focus:ring-0 bg-transparent w-full p-1 transition-colors"
                    placeholder="Identificador del nodo..."
                />
            </div>
            <button @click="deleteCategory(category.id)" class="p-3 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-2xl transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>

        <div class="px-8 flex-1 relative z-10">
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 focus-within:bg-white focus-within:ring-4 focus-within:ring-indigo-500/5 focus-within:border-indigo-200 transition-all">
                <div class="flex items-center justify-between mb-3 opacity-60">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-pulse"></span> Keywords Dataset
                    </label>
                    <span class="text-[9px] font-mono bg-white px-2 py-1 rounded-lg border border-slate-200 shadow-sm">ARRAY_TYPE</span>
                </div>
                <textarea 
                  v-model="category.training_data" 
                  rows="4" 
                  class="w-full bg-transparent border-none focus:ring-0 text-sm font-semibold text-slate-600 placeholder-slate-300 resize-none leading-relaxed"
                  placeholder="vivos, cuyes, reproductores, jovenes..."
                ></textarea>
            </div>
        </div>

        <div class="p-8 pt-6 flex items-center justify-between relative z-10">
            <div class="flex -space-x-2">
                <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[10px] font-bold">IA</div>
                <div class="w-8 h-8 rounded-full border-2 border-white bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-600">✓</div>
            </div>
            <button 
              @click="saveTraining(category)"
              class="bg-slate-900 text-white hover:bg-indigo-600 px-6 py-3 rounded-xl text-xs font-black transition-all flex items-center gap-3 shadow-lg shadow-slate-200 active:scale-95"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
              SINCRONIZAR MODELO
            </button>
        </div>
      </div>
    </div>

    <div v-if="trashed.length > 0" class="mt-24 pb-20">
      <div class="flex items-center gap-6 mb-10">
          <h3 class="text-slate-400 font-black uppercase text-[12px] tracking-[0.4em] whitespace-nowrap">
            Zona de Depuración
          </h3>
          <div class="h-[2px] flex-1 bg-gradient-to-r from-slate-200 to-transparent"></div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="cat in trashed" :key="cat.id" 
             class="bg-white/40 border border-slate-200 p-5 rounded-[1.5rem] flex flex-col gap-4 hover:bg-white transition-all group shadow-sm">
          
          <div class="flex justify-between items-start">
            <div>
              <span class="text-slate-800 font-bold text-base block">{{ cat.name }}</span>
              <span class="text-[10px] text-slate-400 font-mono tracking-widest">UID_{{ cat.id }}</span>
            </div>
            <div class="flex gap-2">
                <button 
                  @click="restoreCategory(cat.id)" 
                  class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm active:scale-90"
                  title="Restaurar"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                  </svg>
                </button>
                <button 
                  @click="permanentDelete(cat.id)" 
                  class="p-2.5 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-600 hover:text-white transition-all active:scale-90"
                  title="Eliminar permanentemente"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
          </div>
        </div>
      </div>
    </div>
   </div>
  </AuthenticatedLayout>
 </template>

<style scoped>
/* Animación suave para el hover de las tarjetas */
.group:hover {
  transform: translateY(-4px);
}
</style>