<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    pig: Object,
    categories: { type: Array, default: () => [] }
});

// --- 1. ESTADO DEL FORMULARIO ---
const form = useForm({
    name: props.pig.name,
    species: props.pig.species || '',
    breed: props.pig.breed || '',
    description: props.pig.description || '',  // ✅ Carga la descripción existente
    price: props.pig.price || 0,
    stock: props.pig.stock || 1,
    specifications: [],
    image: null,
    active: props.pig.active ?? true,
    category_id: props.pig.category_id || null
});

const currentImageUrls = ref([]);
const selectedImages = ref([]);
const imagesToDelete = ref([]); // IDs de imágenes a eliminar

// --- 2. CICLO DE VIDA / CARGA INICIAL ---
onMounted(() => {
    // Cargar todas las imágenes existentes
    if (props.pig.images?.length > 0) {
        currentImageUrls.value = props.pig.images.map(img => {
            const path = img.image_path;
            if (path.startsWith('images/')) return '/' + path;
            if (path.startsWith('/storage/')) return path;
            return '/storage/' + path;
        });
    }

    // Procesar especificaciones: Separar 'Descripción' del resto
    if (Array.isArray(props.pig.specifications)) {
        const specs = props.pig.specifications;
        const descSpec = specs.find(s => s.key.toLowerCase() === 'descripción');
        
        if (descSpec) {
            form.description = descSpec.value;
            form.specifications = specs.filter(s => s.key.toLowerCase() !== 'descripción');
        } else {
            form.specifications = [...specs];
        }
    }
});

// --- 3. ACCIONES DE LA FICHA TÉCNICA ---
const addAttribute = () => form.specifications.push({ key: '', value: '' });
const removeAttribute = (index) => form.specifications.splice(index, 1);

// --- 4. MANEJO DE IMÁGENES ---
const uploadImages = (event) => {
  const files = event.target.files;
  if (files) {
    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      selectedImages.value.push(file);
      currentImageUrls.value.push(URL.createObjectURL(file));
    }
  }
};

const removeImage = (index) => {
  // Si es una imagen existente (tiene ID), agregar a lista de eliminación
  if (props.pig.images[index] && props.pig.images[index].id) {
    imagesToDelete.value.push(props.pig.images[index].id);
  }
  // Si es una imagen nueva seleccionada, eliminar de selectedImages
  if (index < selectedImages.value.length) {
    selectedImages.value.splice(index, 1);
  }
  currentImageUrls.value.splice(index, 1);
};

// --- 4. ENVÍO DEL FORMULARIO ---
const submit = () => {
    // Filtrar atributos vacíos
    let specsToSubmit = form.specifications.filter(s => s.key.trim() !== '');

    // No incluir descripción en specifications - se maneja como campo separado

    // Resumen para el contexto de IA
    const specsSummary = specsToSubmit
        .filter(attr => attr.key && attr.value)
        .map(attr => `${attr.key}: ${attr.value}`)
        .join(', ');

    form.transform((data) => {
        const transformedData = {
            ...data,
            specifications: JSON.stringify(specsToSubmit),
        };

        // Agregar imágenes múltiples si hay
        if (selectedImages.value.length > 0) {
            transformedData.images = selectedImages.value;
        }

        // Agregar IDs de imágenes a eliminar
        if (imagesToDelete.value.length > 0) {
            transformedData.images_to_delete = imagesToDelete.value;
        }

        return transformedData;
    }).put(`/admin/guinea-pigs/${props.pig.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => alert("¡Éxito!"),
        onError: (e) => console.log("Errores:", e)
    });
};
</script>

<template>
    <Head title="Editar Producto" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-950 p-8 rounded-3xl shadow-2xl border-b-8 border-red-700">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-red-700/10 text-red-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-red-700/20">
                        Editor de Productos
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Editar {{ pig.name }}
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Actualiza la información técnica y comercial.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-red-700 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">ID del Producto</span>
                    <p class="text-3xl font-black text-red-400 leading-none">#{{ pig.id }}</p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
                <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-gray-100">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 lg:grid-cols-3">
                            
                            <div class="lg:col-span-1 p-8 lg:p-12 bg-gray-50/50">
                                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                                    <h3 class="text-sm font-bold mb-4 text-gray-700 flex items-center">
                                        <span class="mr-2">🖼️</span> Foto del Producto
                                    </h3>
                                    
                                    <div class="relative group cursor-pointer border-2 border-dashed border-gray-200 rounded-3xl hover:border-red-400 transition-all bg-slate-50" :class="currentImageUrls.length > 0 ? 'p-0' : 'p-8'">
                                        <input type="file" @change="uploadImages" multiple accept="image/*" class="hidden" id="foto-input">
                                        <label for="foto-input" class="flex flex-col items-center cursor-pointer" :class="currentImageUrls.length > 0 ? 'h-full' : ''">
                                            <span v-if="currentImageUrls.length === 0" class="text-5xl mb-3">📷</span>
                                            <p v-if="currentImageUrls.length === 0" class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">Sube fotos (múltiples)</p>

                                            <div v-if="currentImageUrls.length > 0" class="w-full h-full p-4 grid grid-cols-2 gap-2">
                                                <div v-for="(url, index) in currentImageUrls" :key="index" class="relative">
                                                    <img :src="url" class="w-full h-32 object-cover rounded-xl" alt="Imagen" />
                                                    <button @click.prevent="removeImage(index)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-2 p-8 lg:p-12 space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-500 uppercase px-1">Nombre</label>
                                        <input v-model="form.name" type="text" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-red-700 shadow-sm" placeholder="Ej: Cuy Premium" required>
                                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-500 uppercase px-1">Categoría</label>
                                        <select 
                                            v-model="form.category_id" 
                                            class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-red-700 shadow-sm"
                                        >
                                            <option value="">Selecciona una categoría</option>
                                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-500 uppercase px-1">Raza/Modelo/Marca/Tipo</label>
                                        <input v-model="form.breed" type="text" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-red-700 shadow-sm" placeholder="Ej: Merino, Perú...">
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase px-1">Precio (S/.)</label>
                                            <input v-model="form.price" type="number" step="0.01" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-red-700 shadow-sm" required>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase px-1">Stock</label>
                                            <input v-model="form.stock" type="number" min="0" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-red-700 shadow-sm" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex items-center gap-4">
                                    <input type="checkbox" v-model="form.active" id="active" class="w-5 h-5 rounded text-red-600 focus:ring-red-700 border-gray-300">
                                    <label for="active" class="text-sm font-semibold text-gray-700 cursor-pointer">
                                        Producto Activo en Tienda
                                        <span class="text-[10px] text-gray-500 block font-normal italic">Si se desmarca, el producto no aparecerá en el catálogo público.</span>
                                    </label>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-gray-500 uppercase px-1">Descripción</label>
                                    <textarea v-model="form.description" rows="4" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-red-700 shadow-sm" placeholder="Describe las características principales..."></textarea>
                                </div>

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
                                        <Link :href="`/product/${pig.id}`" class="flex-1 sm:flex-none text-center px-6 py-3 text-red-600 hover:text-red-700 text-sm font-bold transition">
                                            👁️ PREVISUALIZAR
                                        </Link>
                                        
                                        <button type="submit" 
                                                :disabled="form.processing"
                                                class="flex-1 sm:flex-none bg-red-700 text-white px-10 py-3 rounded-xl font-black shadow-xl shadow-red-700/20 hover:bg-red-800 hover:-translate-y-0.5 active:translate-y-0 transition-all disabled:opacity-50 disabled:transform-none">
                                            {{ form.processing ? 'GUARDANDO...' : 'GUARDAR CAMBIOS' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
