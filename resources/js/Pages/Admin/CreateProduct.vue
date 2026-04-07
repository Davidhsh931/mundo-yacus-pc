<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

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

const onFileChange = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;
  
  // Validar tipo de archivo
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/tiff'];
  const fileName = file.name.toLowerCase();
  const isImageFile = fileName.endsWith('.jpg') || fileName.endsWith('.jpeg') || fileName.endsWith('.png') || fileName.endsWith('.webp') || fileName.endsWith('.gif') || fileName.endsWith('.bmp') || fileName.endsWith('.tiff');
  
  if (!allowedTypes.includes(file.type) && !isImageFile) {
    alert('❌ Formato no permitido. Usa: JPG, PNG, WebP, GIF, BMP o TIFF.');
    event.target.value = ''; // Limpiar input
    return;
  }
  
  // Validar tamaño (2MB)
  if (file.size > 2 * 1024 * 1024) {
    alert('❌ La imagen no puede pesar más de 2MB.');
    event.target.value = ''; // Limpiar input
    return;
  }
  
  form.image = file;
  previewUrl.value = URL.createObjectURL(file);
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
  <div class="w-full max-w-5xl mx-auto p-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <div class="lg:col-span-1">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
          <h3 class="text-sm font-bold mb-4 text-gray-700 flex items-center">
            <span class="mr-2">🖼️</span> Foto del Producto
          </h3>
          <div class="relative w-full aspect-square bg-gray-50 rounded-xl overflow-hidden border-2 border-dashed border-gray-200 group">
              <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
              <div v-else class="flex flex-col items-center justify-center h-full text-gray-400 text-center p-4">
                <span class="text-3xl mb-2">📸</span>
                <p class="text-xs">Sube una foto clara de lo que vendes</p>
              </div>
          </div>
          <input type="file" @change="onFileChange" class="mt-4 w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
        </div>
      </div>

      <div class="lg:col-span-2">
        <form @submit.prevent="submit" class="space-y-4">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">¿Qué vendes?</label>
              <input v-model="form.name" type="text" class="w-full border-gray-200 rounded-xl text-sm focus:ring-indigo-500" placeholder="Ej: Saco de forraje, Cerdo reproductor...">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Categoría / Tipo</label>
              <div class="flex items-center gap-2 w-full border border-indigo-100 bg-indigo-50/50 rounded-xl px-4 py-2.5 text-sm text-indigo-700 font-medium italic">
                  <span>🤖</span> 
                  <span class="animate-pulse">Clasificación automática activa...</span>
              </div>
              <input v-model="form.species" type="hidden">
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Raza, Marca o Modelo (Opcional)</label>
            <input v-model="form.breed_or_model" type="text" class="w-full border-gray-200 rounded-xl text-sm" placeholder="Ej: Raza Andina, Marca Molitalia...">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Descripción del producto</label>
            <textarea v-model="form.description" rows="3" class="w-full border-gray-200 rounded-xl text-sm" placeholder="Cuenta más detalles sobre lo que ofreces..."></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Precio (S/.)</label>
              <input v-model="form.price" type="number" step="0.10" class="w-full border-gray-200 rounded-xl text-sm">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cantidad</label>
              <input v-model="form.stock" type="number" class="w-full border-gray-200 rounded-xl text-sm">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado</label>
              <div class="flex items-center">
                <input type="checkbox" v-model="form.active" id="active-create" class="rounded text-yellow-500 focus:ring-yellow-500">
                <label for="active-create" class="ml-2 text-sm font-medium text-gray-700">
                  Producto activo
                  <span class="text-xs text-gray-500 block mt-1">
                    (✅ Visible en tienda | ❌ Oculto para clientes)
                  </span>
                </label>
              </div>
            </div>
          </div>

          <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100">
            <h3 class="text-xs font-bold text-indigo-700 mb-3 flex items-center">
               <span class="mr-1">📋</span> Características específicas (Ficha Técnica)
            </h3>
            <div v-for="(attr, index) in form.specifications" :key="index" class="flex gap-2 mb-2">
              <input v-model="attr.key" placeholder="Ej: Peso, Color, Edad" class="flex-1 border-gray-200 rounded-lg text-xs">
              <input v-model="attr.value" placeholder="Valor" class="flex-1 border-gray-200 rounded-lg text-xs">
              <button @click.prevent="removeAttribute(index)" class="text-red-400 hover:text-red-600 px-2">✕</button>
            </div>
            <button @click.prevent="addAttribute" class="mt-2 text-indigo-600 text-xs font-bold flex items-center hover:text-indigo-800">
              <span class="text-lg mr-1">+</span> Añadir otra característica
            </button>
          </div>

          <button 
            type="submit" 
            :disabled="form.processing"
            :class="[
              'relative flex items-center justify-center gap-3 px-8 py-4 font-black rounded-2xl transition-all shadow-lg disabled:opacity-70',
              form.processing ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-indigo-600 hover:bg-indigo-700'
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
