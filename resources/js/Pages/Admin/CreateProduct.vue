<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

// Props para recibir datos de la IA y categorías
const props = defineProps({
    prefillData: { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] }
});

// --- Estado del Formulario Universal ---
const form = useForm({
  name: '',              // Alineado con el controlador
  species: '',           // Alineado con el controlador (category)
  breed_or_model: '',    // Para referencia visual
  description: '',       // Para guardar en specifications
  price: '',
  stock: 1, 
  product_state: 'Disponible',  // Campo requerido por backend
  specifications: [{ key: '', value: '' }], // Para datos técnicos
  image: null,
  active: true, // Por defecto activo
  category_id: null, // Categoría seleccionada manualmente
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
                clase: 'bg-red-50 text-red-700 border border-red-100'
            },
            {
                mensaje: '💡 Tip: La foto se ve un poco oscura. Intenta tomarla cerca de una ventana para que el cuy resalte más.',
                clase: 'bg-red-50 text-red-700 border border-red-100'
            },
            {
                mensaje: '🎨 La imagen podría tener más contraste. Considera usar el modo HDR de tu celular para mejores resultados.',
                clase: 'bg-red-50 text-red-700 border border-red-100'
            },
            {
                mensaje: '📐 Foto aceptable, pero podrías centrar mejor el producto. Recuerda que los clientes compran lo que ven claramente.',
                clase: 'bg-red-50 text-red-700 border border-red-100'
            }
        ];
        
        // Seleccionar un escenario aleatorio (70% positivo, 30% sugerencia)
        const feedback = Math.random() > 0.3 ? escenarios[0] : escenarios[Math.floor(Math.random() * escenarios.length)];
        feedbackFoto.value = feedback;
        
        // Si todo está bien, procesar la imagen
        if (feedback.clase.includes('red')) {
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
        ...data.specifications.filter(attr => attr.key && attr.value && attr.key.toLowerCase() !== 'descripción'),
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
          
          <div class="relative group cursor-pointer border-2 border-dashed border-gray-200 rounded-3xl p-8 hover:border-red-400 transition-all bg-slate-50">
            <input type="file" @change="analizarImagen" class="hidden" id="foto-input">
            <label for="foto-input" class="flex flex-col items-center cursor-pointer">
              <span v-if="!analizandoFoto && !feedbackFoto" class="text-4xl mb-3">📷</span>
              <p v-if="!analizandoFoto && !feedbackFoto" class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">Sube una foto clara</p>
              
              <div v-if="previewUrl" class="w-full aspect-square rounded-xl overflow-hidden">
                <img :src="previewUrl" class="w-full h-full object-cover" />
              </div>
            </label>

            <div v-if="analizandoFoto" class="mt-4 p-3 bg-white rounded-2xl shadow-sm border border-red-100 animate-pulse">
              <p class="text-[10px] text-red-600 font-black uppercase text-center">🧠 Analizando iluminación y enfoque...</p>
            </div>

            <div v-if="feedbackFoto" :class="['mt-4 p-3 rounded-2xl text-[11px] font-medium shadow-sm', feedbackFoto.clase]">
              {{ feedbackFoto.mensaje }}
            </div>
          </div>
          
          <div v-if="!previewUrl" class="mt-4">
            <label for="foto-input" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer block text-center">
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
                    'w-full rounded-xl text-sm focus:ring-red-700 pr-20',
                    camposIALLenados.name ? 'border-red-500 bg-red-50' : 'border-gray-200'
                  ]" 
                  placeholder="Ej: Saco de forraje, Cerdo reproductor..."
                >
                <span v-if="camposIALLenados.name" class="absolute -top-2 -right-2 text-[9px] text-red-600 font-bold animate-pulse bg-white px-2 py-1 rounded-full shadow-md border border-red-200 flex items-center gap-1">
                  <span>🤖</span> IA
                </span>
              </div>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Categoría</label>
              <select 
                v-model="form.category_id" 
                class="w-full border-gray-200 rounded-xl text-sm p-2.5 focus:ring-red-700"
              >
                <option value="">Selecciona una categoría</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Raza, Marca o Modelo (Opcional)</label>
            <input v-model="form.breed_or_model" type="text" class="w-full border-gray-200 rounded-xl text-sm" placeholder="Ej: Raza Andana, Marca Molitalia...">
          </div>

          <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center">
                                            <span class="mr-2">📑</span> DESCRIPCIÓN DEL PRODUCTO
                                        </h3>
                                    </div>
                                    
                                    <div class="space-y-1 relative">
                                        <label class="block text-xs font-bold text-gray-500 uppercase px-1">Descripción</label>
                                        <textarea 
                                            v-model="form.description" 
                                            rows="4" 
                                            :class="[
                                                'w-full rounded-xl text-sm p-3 focus:ring-red-700 shadow-sm',
                                                camposIALLenados.description ? 'border-red-500 bg-red-50' : 'border-gray-200 bg-white'
                                            ]" 
                                            placeholder="Describe las características principales..."
                                        ></textarea>
                                        <span v-if="camposIALLenados.description" class="absolute -top-2 -right-2 text-[9px] text-red-600 font-bold animate-pulse bg-white px-2 py-1 rounded-full shadow-md border border-red-200 flex items-center gap-1">
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
                    camposIALLenados.price ? 'border-red-500 bg-red-50' : 'border-gray-200'
                  ]"
                >
                <span v-if="camposIALLenados.price" class="absolute -top-2 -right-2 text-[9px] text-red-600 font-bold animate-pulse bg-white px-2 py-1 rounded-full shadow-md border border-red-200 flex items-center gap-1">
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
                <input type="checkbox" v-model="form.active" id="active-create" class="rounded text-red-600 focus:ring-red-700">
                <label for="active-create" class="ml-2 text-sm font-medium text-gray-700">
                  Producto activo
                  <span class="text-xs text-gray-500 block mt-1">
                    (✅ Visible en tienda | ❌ Oculto para clientes)
                  </span>
                </label>
              </div>
            </div>
          </div>
          
          <!-- Campo oculto para product_state requerido por backend -->
          <input type="hidden" v-model="form.product_state">

          <div class="space-y-4">
                                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center">
                                            <span class="mr-2">📋</span> Ficha Técnica
                                        </h3>
                                        <button type="button" @click="addAttribute" class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[10px] font-bold hover:bg-red-200 transition">
                                            + AGREGAR ATRIBUTO
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div v-for="(attr, index) in form.specifications" :key="index" class="flex gap-2 group">
                                            <input v-model="attr.key" type="text" placeholder="Ej: Peso" class="flex-1 border-gray-200 rounded-xl text-[13px] p-2 focus:ring-red-700 shadow-sm">
                                            <input v-model="attr.value" type="text" placeholder="Ej: 1.2kg" class="flex-1 border-gray-200 rounded-xl text-[13px] p-2 focus:ring-red-700 shadow-sm">
                                            <button type="button" @click="removeAttribute(index)" class="text-gray-300 hover:text-red-500 transition px-2">
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>

          <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-12 pt-8 border-t border-gray-100">
                                    <Link href="/admin/guinea-pigs" class="text-gray-400 hover:text-gray-600 text-sm font-bold transition">
                                        ← CANCELAR
                                    </Link>
                                    
                                    <div class="flex gap-3 w-full sm:w-auto">
                                        <button type="submit" 
                                                :disabled="form.processing"
                                                class="flex-1 sm:flex-none bg-red-700 text-white px-10 py-3 rounded-xl font-bold shadow-xl shadow-red-700/20 hover:bg-red-800 hover:-translate-y-0.5 active:translate-y-0 transition-all disabled:opacity-50 disabled:transform-none">
                                            {{ form.processing ? 'PUBLICANDO...' : 'PUBLICAR EN MUNDO YACUS' }}
                                        </button>
                                    </div>
                                </div>
        </form>
      </div>
    </div>
  </div>
</template>
