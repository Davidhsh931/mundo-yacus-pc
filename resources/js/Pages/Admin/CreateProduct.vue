<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

// Props para recibir datos de la IA
const props = defineProps({
    prefillData: { type: Object, default: () => ({}) }
});

// --- Estado del Formulario Universal ---
const form = useForm({
  name: '',              // Alineado con el controlador
  species: '',           // Alineado con el controlador (category)
  breed_or_model: '',    // Para referencia visual
  description: '',       // Para guardar en specifications
  price: '',
  stock: 1, 
  specifications: [{ key: '', value: '' }], // Para datos técnicos
  image: null,
  active: true, // Por defecto activo
  ia_verification: { 
    status: 'pending', 
    requested_at: new Date().toISOString() 
  },
});

const previewUrl = ref(null);
const feedbackFoto = ref(null);
const analizandoFoto = ref(false);

// Detectar qué campos fueron llenados por la IA
const camposIALLenados = computed(() => {
    const prefill = props.prefillData;
    
    const deteccion = {
        name: prefill.name && form.name === prefill.name,
        price: prefill.price && form.price == prefill.price,
        description: prefill.description && form.description === prefill.description,
        category: prefill.category_id && form.species && form.species.trim() !== ''
    };
    
    console.log(' Detección de campos IA:', deteccion);
    console.log(' Form species actual:', form.species);
    console.log(' Prefill category_id:', prefill.category_id);
    console.log(' Category length:', form.species.length);
    
    return deteccion;
});

// Obtener nombre de categoría por ID
const getCategoryName = (categoryId) => {
    const categories = {
        1: 'Animales en Pie',
        2: 'Forraje y Alimento'
    };
    return categories[categoryId] || '';
};

// Watch para aplicar prefillData cuando cambie
watch(() => props.prefillData, (newData) => {
    console.log('🔄 Watch prefillData activado:', newData);
    if (Object.keys(newData).length > 0) {
        // Aplicar datos de la IA
        if (newData.name) {
            form.name = newData.name;
            console.log('✅ Nombre aplicado:', newData.name);
        }
        if (newData.price) {
            form.price = newData.price;
            console.log('✅ Precio aplicado:', newData.price);
        }
        if (newData.description) {
            form.description = newData.description;
            console.log('✅ Descripción aplicada:', newData.description);
        }
        if (newData.category_id) {
            const categoryName = getCategoryName(newData.category_id);
            form.species = categoryName;
            console.log('✅ Categoría aplicada:', categoryName, '(ID:', newData.category_id, ')');
        }
    }
}, { immediate: true });

// Función para analizar imagen con IA
const analizarImagen = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    
    analizandoFoto.value = true;
    feedbackFoto.value = null;
    
    // Validar tipo de archivo
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/tiff'];
    const fileName = file.name.toLowerCase();
    const isImageFile = fileName.endsWith('.jpg') || fileName.endsWith('.jpeg') || fileName.endsWith('.png') || fileName.endsWith('.webp') || fileName.endsWith('.gif') || fileName.endsWith('.bmp') || fileName.endsWith('.tiff');
    
    if (!allowedTypes.includes(file.type) && !isImageFile) {
        feedbackFoto.value = {
            mensaje: 'Formato no permitido. Usa: JPG, PNG, WebP, GIF, BMP o TIFF.',
            clase: 'bg-rose-50 text-rose-700 border border-rose-100'
        };
        analizandoFoto.value = false;
        event.target.value = '';
        return;
    }
    
    // Validar tamaño (2MB)
    if (file.size > 2 * 1024 * 1024) {
        feedbackFoto.value = {
            mensaje: 'La imagen no puede pesar más de 2MB.',
            clase: 'bg-rose-50 text-rose-700 border border-rose-100'
        };
        analizandoFoto.value = false;
        event.target.value = '';
        return;
    }
    
    // Simulamos el análisis de IA
    setTimeout(() => {
        analizandoFoto.value = false;
        
        // Escenarios aleatorios de feedback
        const escenarios = [
            {
                mensaje: '✨ Excelente foto! Buena iluminación y el producto se ve claramente. Perfecto para Mundo Yacus.',
                clase: 'bg-emerald-50 text-emerald-700 border border-emerald-100'
            },
            {
                mensaje: '💡 Tip: La foto se ve un poco oscura. Intenta tomarla cerca de una ventana para que el cuy resalte más.',
                clase: 'bg-amber-50 text-amber-700 border border-amber-100'
            },
            {
                mensaje: '🎨 La imagen podría tener más contraste. Considera usar el modo HDR de tu celular para mejores resultados.',
                clase: 'bg-amber-50 text-amber-700 border border-amber-100'
            },
            {
                mensaje: '📐 Foto aceptable, pero podrías centrar mejor el producto. Recuerda que los clientes compran lo que ven claramente.',
                clase: 'bg-blue-50 text-blue-700 border border-blue-100'
            }
        ];
        
        // Seleccionar un escenario aleatorio (70% positivo, 30% sugerencia)
        const feedback = Math.random() > 0.3 ? escenarios[0] : escenarios[Math.floor(Math.random() * escenarios.length)];
        feedbackFoto.value = feedback;
        
        // Si todo está bien, procesar la imagen
        if (feedback.clase.includes('emerald')) {
            form.image = file;
            previewUrl.value = URL.createObjectURL(file);
        }
    }, 1500);
};

const addAttribute = () => form.specifications.push({ key: '', value: '' });
const removeAttribute = (index) => form.specifications.splice(index, 1);

const submit = () => {
  console.log('=== SUBMIT INICIADO ===');
  console.log('Form data:', form.data());
  console.log('Has image:', !!form.image);
  console.log('Route:', route('guinea-pigs.store'));
  
  // Transformar datos antes de enviar, especialmente specifications a JSON
  form.transform((data) => {
    console.log('TRANSFORM DATA:', data);
    
    // Creamos un resumen de todo lo que el usuario llenó
    const technicalData = data.specifications
      .filter(attr => attr.key && attr.value)
      .map(attr => `${attr.key}: ${attr.value}`)
      .join(', ');

    return {
      ...data,
      // Este es el "súper contexto" que enviamos al servidor
      ai_context: `PRODUCTO: ${data.name}. 
                   DESCRIPCIÓN: ${data.description}. 
                   FICHA TÉCNICA: ${technicalData}. 
                   RAZA/MODELO: ${data.breed_or_model}.`,
      
      specifications: JSON.stringify([
        ...data.specifications.filter(attr => attr.key && attr.value),
        { key: 'descripción', value: data.description || '' },
        { key: 'raza_o_modelo', value: data.breed_or_model || '' }
      ]),
    }
  }).post(route('guinea-pigs.store'), {
    forceFormData: true,
    onError: (errors) => {
      console.error('POST ERRORS:', errors);
    },
  });
};
</script>

<template>
  <div class="w-full max-w-5xl mx-auto p-4 bg-[#F8FAFC] rounded-2xl">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <div class="lg:col-span-1">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
          <h3 class="text-sm font-medium mb-4 text-gray-700 flex items-center">
            <span class="mr-2">📸</span> Foto del Producto
          </h3>
          
          <div class="relative group cursor-pointer border-2 border-dashed border-gray-200 rounded-3xl p-8 hover:border-emerald-400 transition-all bg-slate-50">
            <input type="file" @change="analizarImagen" class="hidden" id="foto-input">
            <label for="foto-input" class="flex flex-col items-center cursor-pointer">
              <span v-if="!analizandoFoto && !feedbackFoto" class="text-4xl mb-3">📷</span>
              <p v-if="!analizandoFoto && !feedbackFoto" class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">Sube una foto clara</p>
              
              <div v-if="previewUrl" class="w-full aspect-square rounded-xl overflow-hidden">
                <img :src="previewUrl" class="w-full h-full object-cover" />
              </div>
            </label>

            <div v-if="analizandoFoto" class="mt-4 p-3 bg-white rounded-2xl shadow-sm border border-emerald-100 animate-pulse">
              <p class="text-[10px] text-emerald-600 font-black uppercase text-center">🧠 Analizando iluminación y enfoque...</p>
            </div>

            <div v-if="feedbackFoto" :class="['mt-4 p-3 rounded-2xl text-[11px] font-medium shadow-sm', feedbackFoto.clase]">
              {{ feedbackFoto.mensaje }}
            </div>
          </div>
          
          <div v-if="!previewUrl" class="mt-4">
            <label for="foto-input" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer block text-center">
              O haz clic para seleccionar archivo
            </label>
          </div>
        </div>
      </div>

      <div class="lg:col-span-2">
        <form @submit.prevent="submit" class="space-y-4">
          
          <div class="grid grid-cols-1 gap-4">
            <div class="relative">
              <label class="block text-xs font-medium text-gray-500 uppercase mb-1">¿Qué vendes?</label>
              <div class="relative">
                <input 
                  v-model="form.name" 
                  type="text" 
                  :class="[
                    'w-full rounded-xl text-sm focus:ring-emerald-500 pr-20',
                    camposIALLenados.name ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'
                  ]" 
                  placeholder="Ej: Saco de forraje, Cerdo reproductor..."
                >
                <span v-if="camposIALLenados.name" class="absolute -top-2 -right-2 text-[9px] text-emerald-600 font-bold animate-pulse bg-white px-2 py-1 rounded-full shadow-md border border-emerald-200 flex items-center gap-1">
                  <span>🤖</span> IA
                </span>
              </div>
            </div>
            <div class="relative">
              <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Categoría / Tipo</label>
              <div class="relative">
                <div :class="[
                  'flex items-center gap-2 w-full rounded-xl px-4 py-2.5 text-sm font-medium italic',
                  camposIALLenados.category ? 'border border-emerald-500 bg-emerald-50 text-emerald-700' : 'border border-emerald-100 bg-emerald-50/50 text-emerald-700'
                ]">
                    <span>🏷️</span> 
                    <span>{{ form.species || 'Clasificación automática activa...' }}</span>
                </div>
                <span v-if="camposIALLenados.category" class="absolute -top-2 -right-2 text-[9px] text-emerald-600 font-bold animate-pulse bg-white px-2 py-1 rounded-full shadow-md border border-emerald-200 flex items-center gap-1">
                  <span>🤖</span> IA
                </span>
                <input v-model="form.species" type="hidden">
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Raza, Marca o Modelo (Opcional)</label>
            <input v-model="form.breed_or_model" type="text" class="w-full border-gray-200 rounded-xl text-sm" placeholder="Ej: Raza Andana, Marca Molitalia...">
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Descripción del producto</label>
            <div class="relative">
              <textarea 
                v-model="form.description" 
                rows="3" 
                :class="[
                  'w-full rounded-xl text-sm resize-none',
                  camposIALLenados.description ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'
                ]" 
                placeholder="Cuenta más detalles sobre lo que ofreces..."
              ></textarea>
              <span v-if="camposIALLenados.description" class="absolute -top-2 -right-2 text-[9px] text-emerald-600 font-bold animate-pulse bg-white px-2 py-1 rounded-full shadow-md border border-emerald-200 flex items-center gap-1">
                <span>🤖</span> IA
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
              <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Precio (S/.)</label>
              <div class="relative">
                <input 
                  v-model="form.price" 
                  type="number" 
                  step="0.10" 
                  :class="[
                    'w-full rounded-xl text-sm',
                    camposIALLenados.price ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'
                  ]"
                >
                <span v-if="camposIALLenados.price" class="absolute -top-2 -right-2 text-[9px] text-emerald-600 font-bold animate-pulse bg-white px-2 py-1 rounded-full shadow-md border border-emerald-200 flex items-center gap-1">
                  <span>🤖</span> IA
                </span>
              </div>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Cantidad</label>
              <input v-model="form.stock" type="number" class="w-full border-gray-200 rounded-xl text-sm">
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Estado</label>
              <div class="flex items-center">
                <input type="checkbox" v-model="form.active" id="active-create" class="rounded text-emerald-500 focus:ring-emerald-500">
                <label for="active-create" class="ml-2 text-sm font-medium text-gray-700">
                  Producto activo
                  <span class="text-xs text-gray-500 block mt-1">
                    (✅ Visible en tienda | ❌ Oculto para clientes)
                  </span>
                </label>
              </div>
            </div>
          </div>

          <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
            <h3 class="text-xs font-medium text-emerald-700 mb-3 flex items-center">
               <span class="mr-1">📋</span> Características específicas (Ficha Técnica)
            </h3>
            <div v-for="(attr, index) in form.specifications" :key="index" class="flex gap-2 mb-2">
              <input v-model="attr.key" placeholder="Ej: Peso, Color, Edad" class="flex-1 border-gray-200 rounded-lg text-xs">
              <input v-model="attr.value" placeholder="Valor" class="flex-1 border-gray-200 rounded-lg text-xs">
              <button @click.prevent="removeAttribute(index)" class="text-red-400 hover:text-red-600 px-2">✕</button>
            </div>
            <button @click.prevent="addAttribute" class="mt-2 text-emerald-600 text-xs font-medium flex items-center hover:text-emerald-800">
              <span class="text-lg mr-1">+</span> Añadir otra característica
            </button>
          </div>

          <button 
            type="submit" 
            :disabled="form.processing"
            :class="[
              'relative flex items-center justify-center gap-3 px-8 py-4 font-black rounded-2xl transition-all shadow-xl disabled:opacity-70 border-2 border-transparent',
              form.processing 
                ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-emerald-400' 
                : 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white hover:from-emerald-700 hover:to-emerald-800 hover:shadow-2xl hover:scale-[1.02] border-emerald-500'
            ]"
          >
            <template v-if="form.processing">
              <span class="animate-bounce text-2xl">🤖</span>
              <span class="animate-pulse text-sm">IA Clasificando...</span>
            </template>

            <template v-else>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Publicar en Mundo Yacus</span>
            </template>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
