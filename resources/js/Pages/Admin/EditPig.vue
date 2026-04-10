<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

const props = defineProps({
    pig: Object
});

// --- 1. ESTADO DEL FORMULARIO ---
const form = useForm({
    name: props.pig.name,
    species: props.pig.species || '',
    breed_or_model: props.pig.breed || '',
    description: '',
    price: props.pig.price || 0,
    stock: props.pig.stock || 1,
    specifications: [],
    image: null,
    active: props.pig.active ?? true
});

const currentImageUrl = ref(null);
const image = ref(null);
const croppedImage = ref(null);

// --- 2. CICLO DE VIDA / CARGA INICIAL ---
onMounted(() => {
    // Cargar imagen actual si existe
    if (props.pig.images?.length > 0) {
        currentImageUrl.value = `/storage/${props.pig.images[0].image_path}`;
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

// --- 4. MANEJO DE IMAGEN Y RECORTA ---
const uploadImage = (event) => {
  const file = event.target.files[0];
  if (file) {
    image.value = URL.createObjectURL(file);
  }
};

const onChange = ({ canvas }) => {
  const resizedCanvas = document.createElement('canvas');
  resizedCanvas.width = 703;   // Ancho exacto
  resizedCanvas.height = 450;  // Alto exacto
  
  const ctx = resizedCanvas.getContext('2d');
  ctx.drawImage(canvas, 0, 0, 703, 450);

  resizedCanvas.toBlob((blob) => {
    form.image = blob;
    croppedImage.value = resizedCanvas.toDataURL('image/jpeg');
  }, 'image/jpeg', 0.9);
};

// --- 4. ENVÍO DEL FORMULARIO ---
const submit = () => {
    // Filtrar atributos vacíos
    let specsToSubmit = form.specifications.filter(s => s.key.trim() !== '');

    // Incluir descripción en la ficha técnica para el backend
    if (form.description?.trim()) {
        specsToSubmit.push({ key: 'descripción', value: form.description.trim() });
    }

    // Resumen para el contexto de IA
    const specsSummary = specsToSubmit
        .filter(attr => attr.key && attr.value)
        .map(attr => `${attr.key}: ${attr.value}`)
        .join(', ');

    form.transform((data) => ({
        ...data,
        specifications: JSON.stringify(specsToSubmit),
    })).put(`/admin/guinea-pigs/${props.pig.id}`, { // <--- Cambiado a .put y quitado _method
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
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-950 p-8 rounded-3xl shadow-2xl border-b-8 border-yellow-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-yellow-500/20">
                        Editor de Productos
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Editar {{ pig.name }}
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Actualiza la información técnica y comercial.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-yellow-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">ID del Producto</span>
                    <p class="text-3xl font-black text-yellow-400 leading-none">#{{ pig.id }}</p>
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
                                    
                                    <div v-if="currentImageUrl && !image" class="mb-4 text-center">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase mb-2 text-left">Imagen en servidor</p>
                                        <div class="w-full aspect-square bg-gray-50 rounded-xl overflow-hidden border-2 border-gray-100 shadow-inner">
                                            <img :src="currentImageUrl" class="w-full h-full object-cover" alt="Actual" />
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <label class="block w-full py-3 px-4 bg-yellow-500 text-gray-950 text-center rounded-xl font-bold cursor-pointer hover:bg-gray-950 hover:text-yellow-400 transition shadow-lg shadow-yellow-500/20">
                                            {{ image ? 'Cambiar Foto' : 'Subir Nueva Foto' }}
                                            <input type="file" @change="uploadImage" accept="image/*" class="hidden">
                                        </label>
                                    </div>

                                    <div v-if="image" class="mt-6 p-2 bg-white rounded-2xl border-2 border-dashed border-yellow-400 overflow-hidden">
                                        <cropper
                                            :src="image"
                                            :stencil-props="{
                                                aspectRatio: 703/450 
                                            }" 
                                            @change="onChange"
                                            class="h-64 w-full"
                                        />
                                        <p class="text-[10px] text-center text-yellow-600 font-bold mt-2 uppercase">
                                            ↔️ Ajusta tu imagen al recuadro
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-2 p-8 lg:p-12 space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-500 uppercase px-1">Nombre</label>
                                        <input v-model="form.name" type="text" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-yellow-500 shadow-sm" placeholder="Ej: Cuy Premium" required>
                                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-500 uppercase px-1">Categoría</label>
                                        <div class="flex items-center gap-2 border border-yellow-100 bg-yellow-50/50 rounded-xl px-4 py-3 text-xs text-yellow-700 font-medium italic">
                                            <span>🤖</span> <span class="animate-pulse">Clasificación automática activa</span>
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-500 uppercase px-1">Raza o Modelo</label>
                                        <input v-model="form.breed_or_model" type="text" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-yellow-500 shadow-sm" placeholder="Ej: Merino, Perú...">
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase px-1">Precio (S/.)</label>
                                            <input v-model="form.price" type="number" step="0.01" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-yellow-500 shadow-sm" required>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase px-1">Stock</label>
                                            <input v-model="form.stock" type="number" min="0" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-yellow-500 shadow-sm" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex items-center gap-4">
                                    <input type="checkbox" v-model="form.active" id="active" class="w-5 h-5 rounded text-yellow-500 focus:ring-yellow-500 border-gray-300">
                                    <label for="active" class="text-sm font-semibold text-gray-700 cursor-pointer">
                                        Producto Activo en Tienda
                                        <span class="text-[10px] text-gray-500 block font-normal italic">Si se desmarca, el producto no aparecerá en el catálogo público.</span>
                                    </label>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-gray-500 uppercase px-1">Descripción</label>
                                    <textarea v-model="form.description" rows="4" class="w-full border-gray-200 rounded-xl text-sm p-3 focus:ring-yellow-500 shadow-sm" placeholder="Describe las características principales..."></textarea>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center">
                                            <span class="mr-2">📋</span> Ficha Técnica
                                        </h3>
                                        <button type="button" @click="addAttribute" class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-[10px] font-bold hover:bg-yellow-200 transition">
                                            + AGREGAR ATRIBUTO
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div v-for="(attr, index) in form.specifications" :key="index" class="flex gap-2 group">
                                            <input v-model="attr.key" type="text" placeholder="Ej: Peso" class="flex-1 border-gray-200 rounded-xl text-[13px] p-2 focus:ring-yellow-500 shadow-sm">
                                            <input v-model="attr.value" type="text" placeholder="Ej: 1.2kg" class="flex-1 border-gray-200 rounded-xl text-[13px] p-2 focus:ring-yellow-500 shadow-sm">
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
                                        <Link :href="`/product/${pig.id}`" class="flex-1 sm:flex-none text-center px-6 py-3 text-yellow-600 hover:text-yellow-700 text-sm font-bold transition">
                                            👁️ PREVISUALIZAR
                                        </Link>
                                        
                                        <button type="submit" 
                                                :disabled="form.processing"
                                                class="flex-1 sm:flex-none bg-yellow-500 text-gray-950 px-10 py-3 rounded-xl font-black shadow-xl shadow-yellow-500/20 hover:bg-gray-950 hover:text-yellow-400 hover:-translate-y-0.5 active:translate-y-0 transition-all disabled:opacity-50 disabled:transform-none">
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
