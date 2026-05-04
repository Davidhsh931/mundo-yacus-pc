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
  breed: '',             // Campo real de la base de datos
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

const previewUrls = ref([]);
const selectedImages = ref([]);

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

// Función para subir múltiples imágenes
const uploadImages = (event) => {
  const files = event.target.files;
  if (files) {
    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      selectedImages.value.push(file);
      previewUrls.value.push(URL.createObjectURL(file));
    }
  }
};

// Función para eliminar una imagen específica
const removeImage = (index) => {
  selectedImages.value.splice(index, 1);
  previewUrls.value.splice(index, 1);
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
    console.log('Selected images:', selectedImages.value);

    // Creamos un resumen de todo lo que el usuario llenó
    const technicalData = data.specifications
      .filter(attr => attr.key && attr.value)
      .map(attr => `${attr.key}: ${attr.value}`)
      .join(', ');

    const transformedData = {
      ...data,
      // Este es el "súper contexto" que enviamos al servidor
      ai_context: `PRODUCTO: ${data.name}.
                   DESCRIPCIÓN: ${data.description}.
                   FICHA TÉCNICA: ${technicalData}.
                   RAZA/MODELO: ${data.breed}.`,

      specifications: JSON.stringify([
        ...data.specifications.filter(attr => attr.key && attr.value && attr.key.toLowerCase() !== 'descripción')
      ]),
    };

    // Agregar imágenes múltiples si hay
    if (selectedImages.value.length > 0) {
      transformedData.images = selectedImages.value;
    }

    return transformedData;
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
          
          <div class="relative group cursor-pointer border-2 border-dashed border-gray-200 rounded-3xl hover:border-red-400 transition-all bg-slate-50" :class="previewUrls.length > 0 ? 'p-0' : 'p-8'">
            <input type="file" @change="uploadImages" multiple class="hidden" id="foto-input">
            <label for="foto-input" class="flex flex-col items-center cursor-pointer" :class="previewUrls.length > 0 ? 'h-full' : ''">
              <span v-if="previewUrls.length === 0" class="text-5xl mb-3">📷</span>
              <p v-if="previewUrls.length === 0" class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">Sube fotos (múltiples)</p>

              <div v-if="previewUrls.length > 0" class="w-full h-full p-4 grid grid-cols-2 gap-2">
                <div v-for="(url, index) in previewUrls" :key="index" class="relative">
                  <img :src="url" class="w-full h-32 object-cover rounded-xl" />
                  <button @click.prevent="removeImage(index)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                </div>
              </div>
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
            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Raza/Modelo/Marca/Tipo(Opcional)</label>
            <input v-model="form.breed" type="text" class="w-full border-gray-200 rounded-xl text-sm" placeholder="Ej: Raza Andana, Marca Molitalia...">
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
