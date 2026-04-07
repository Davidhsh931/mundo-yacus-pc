<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductGallery from '../Components/ProductGallery.vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    pig: Object,
    isAdmin: Boolean,
    auth: Object,
    canComment: Boolean
});

// Formulario para el nuevo comentario
const form = useForm({
    guinea_pig_id: props.pig.id,
    content: '',
    rating: 5
});

const submitComment = () => {
    form.post(route('comments.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('content'),
    });
};

const deleteComment = (id) => {
    if (confirm('¿Estás seguro de eliminar este comentario?')) {
        router.delete(route('comments.destroy', id), {
            preserveScroll: true
        });
    }
};

// COMPRENSION DE IMÁGENES BLINDADA
const FALLBACK_IMAGE = '/images/cobaya-fondo-blanco.jpg';
const isInvalidImageValue = (value) => {
    if (value === null || value === undefined) return true;
    if (value === 0 || value === '0') return true;
    if (typeof value === 'string' && value.trim() === '') return true;
    return false;
};

const formattedImages = computed(() => {
    // Si no hay imágenes, creamos un objeto de respaldo para evitar errores en el carrusel
    if (!props.pig.images || props.pig.images.length === 0) {
        return [{
            id: 'placeholder',
            image_path: FALLBACK_IMAGE
        }];
    }

    return props.pig.images.map(img => {
        let path = img.image_path;
        if (isInvalidImageValue(path)) path = FALLBACK_IMAGE;
        else if (typeof path === 'string' && path.startsWith('http')) path = path;
        else if (typeof path === 'string') path = path.startsWith('/storage/') ? path : '/storage/' + path.replace(/^\/?storage\/?/, '');
        else path = FALLBACK_IMAGE;
        return { ...img, image_path: path };
    });
});

const addToCart = () => {
    if (props.pig.stock <= 0) {
        alert('❌ Este producto está agotado. No hay stock disponible.');
        return;
    }
    router.post('/cart/add/' + props.pig.id, {}, {
        onSuccess: (page) => {
            // Mostrar mensaje de éxito o error del backend
            if (page.props.flash?.success) {
                alert(page.props.flash.success);
            } else if (page.props.flash?.error) {
                alert(page.props.flash.error);
            } else {
                alert('¡' + props.pig.name + ' ya está en tu carrito! 🐹🛒');
            }
        },
    });
};
</script>

<template>
    <Head :title="pig.name + ' - Mundo Yacus'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-950 p-4 sm:p-6 lg:p-8 rounded-3xl shadow-2xl border-b-8 border-yellow-500">
                <div class="w-full md:w-auto">
                    <span class="inline-block px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-yellow-500/20">
                        Detalle del Producto
                    </span>
                    <h2 class="font-black text-2xl sm:text-3xl lg:text-4xl text-white leading-none tracking-tighter">
                        {{ pig.name }}
                    </h2>
                    <p class="text-gray-400 text-xs sm:text-sm mt-1">Conoce todas las características de este producto.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-yellow-500 pl-4 md:pl-0 md:pr-4 w-full md:w-auto">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Disponibilidad</span>
                    <p class="text-2xl sm:text-3xl font-black leading-none" :class="pig.stock > 0 ? 'text-green-400' : 'text-red-400'">
                        {{ pig.stock > 0 ? pig.stock + ' unidades' : 'AGOTADO' }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
                
                <!-- Producto Principal -->
                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 overflow-hidden border border-gray-100">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                        
                        <!-- Galería de Imágenes -->
                        <div class="p-4 sm:p-6 lg:p-12 bg-gray-50/50 order-1 lg:order-1">
                            <ProductGallery :images="formattedImages" />
                        </div>

                        <!-- Información del Producto -->
                        <div class="p-4 sm:p-6 lg:p-12 space-y-6 order-2 lg:order-2">
                            <div>
                                <div v-if="isAdmin" class="mb-6">
                                    <Link :href="`/admin/guinea-pigs/${pig.id}/edit`" 
                                          class="bg-yellow-500 text-gray-950 px-4 sm:px-6 py-3 rounded-xl font-black hover:bg-gray-950 hover:text-yellow-400 transition flex items-center gap-2 w-full sm:w-fit justify-center sm:justify-start">
                                        ✏️ Editar Producto
                                    </Link>
                                </div>
                                
                                <span 
                                    :class="{
                                        'bg-green-100 text-green-700 border-green-200': pig.category?.name === 'Animal Vivo',
                                        'bg-red-100 text-red-700 border-red-200': pig.category?.name === 'Carne Beneficiada',
                                        'bg-blue-100 text-blue-700 border-blue-200': pig.category?.name === 'Otros/Insumos',
                                        'bg-gray-100 text-gray-700 border-gray-200': !pig.category
                                    }" 
                                    class="px-3 sm:px-4 py-1.5 rounded-full text-[10px] sm:text-xs font-black uppercase tracking-widest border flex items-center gap-2 inline-flex"
                                >
                                    <span v-if="pig.category?.name === 'Animal Vivo'">🐹</span>
                                    <span v-else-if="pig.category?.name === 'Carne Beneficiada'">🥩</span>
                                    <span v-else-if="pig.category?.name === 'Otros/Insumos'">📦</span>
                                    <span v-else>📋</span>
                                    {{ pig.category ? pig.category.name : 'Sin categoría' }}
                                </span>
                                
                                <h1 class="text-5xl font-black text-gray-950 mt-4 tracking-tight group-hover:text-yellow-600 transition-colors">{{ pig.name }}</h1>
                                <p class="text-yellow-500 font-bold flex items-center mt-2">
                                    <span class="mr-2 text-lg">📍</span> Chacras de Yacus, Perú
                                </p>
                                
                                <!-- Descripción del producto -->
                                <div v-if="pig.description" class="mt-6 p-6 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl border border-yellow-100">
                                    <h3 class="text-xs font-black text-yellow-700 uppercase tracking-[0.2em] mb-3 flex items-center">
                                        <span class="mr-2">📝</span> Descripción del Producto
                                    </h3>
                                    <p class="text-gray-700 leading-relaxed text-lg">{{ pig.description }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-3xl border border-gray-100 inline-flex">
                                <span class="text-4xl font-black text-gray-950">S/ {{ pig.price }}</span>
                                <div class="h-8 w-[1px] bg-gray-300"></div>
                                <span class="text-gray-500 font-bold text-sm">IVA incluido</span>
                            </div>

                            <div v-if="pig.specifications?.length" class="space-y-4">
                                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center">
                                    📋 Especificaciones Técnicas
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div v-for="(attr, index) in pig.specifications" :key="index" 
                                         class="p-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
                                        <p class="text-[9px] text-gray-400 uppercase font-black tracking-tighter mb-1">{{ attr.key }}</p>
                                        <p class="font-bold text-gray-800 text-sm">{{ attr.value }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón de Compra -->
                            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                                <div class="flex flex-col">
                                    <span class="text-[9px] text-gray-400 font-black uppercase tracking-tighter leading-none">Inversión Total</span>
                                    <span class="text-3xl font-black text-gray-950">S/ {{ pig.price }}</span>
                                </div>
                                <button @click="addToCart" 
                                        :disabled="pig.stock <= 0" 
                                        class="bg-yellow-500 text-gray-950 w-14 h-14 rounded-2xl flex items-center justify-center hover:bg-gray-950 hover:text-yellow-400 disabled:bg-gray-200 transition-all shadow-lg hover:shadow-yellow-100 active:scale-90">
                                    <span v-if="pig.stock > 0" class="text-2xl">🛒</span>
                                    <span v-else class="text-[10px] font-extrabold text-gray-400">FIN</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Comentarios -->
                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 overflow-hidden border border-gray-100">
                    <div class="p-8 lg:p-12">
                        <h3 class="text-2xl font-black text-gray-950 mb-8 flex items-center gap-3">
                            <span class="text-3xl">💬</span>
                            Opiniones de la Comunidad
                        </h3>

                        <!-- Formulario para nuevo comentario -->
                        <div v-if="canComment" class="mb-12 p-6 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl border border-yellow-100">
                            <h4 class="text-lg font-black text-gray-950 mb-4">Comparte tu experiencia</h4>
                            <form @submit.prevent="submitComment" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-black text-gray-700 mb-2">Tu opinión</label>
                                    <textarea
                                        v-model="form.content"
                                        rows="3"
                                        class="w-full border-gray-200 rounded-xl p-3 focus:ring-yellow-500 focus:border-yellow-500"
                                        placeholder="Cuéntanos tu experiencia con este producto..."
                                        required
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-black text-gray-700 mb-2">Calificación</label>
                                    <div class="flex gap-2">
                                        <button v-for="star in 5" :key="star" type="button" 
                                                @click="form.rating = star"
                                                class="text-2xl transition-colors"
                                                :class="star <= form.rating ? 'text-yellow-400' : 'text-gray-300'">
                                            ⭐
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" 
                                        :disabled="form.processing"
                                        class="bg-yellow-500 text-gray-950 px-6 py-3 rounded-xl font-black hover:bg-gray-950 hover:text-yellow-400 transition disabled:opacity-50">
                                    {{ form.processing ? 'Enviando...' : 'Enviar Opinión' }}
                                </button>
                            </form>
                        </div>

                        <!-- Lista de comentarios -->
                        <div class="space-y-6">
                            <div v-for="comment in pig.comments" :key="comment.id" 
                                 class="p-6 bg-gray-50 rounded-2xl border border-gray-100">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600 font-black">
                                            {{ comment.user?.name?.substring(0, 2).toUpperCase() || '??' }}
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-950">{{ comment.user?.name }}</p>
                                            <p class="text-xs text-gray-400">{{ new Date(comment.created_at).toLocaleDateString() }}</p>
                                        </div>
                                    </div>
                                    <button v-if="auth.user?.id === comment.user_id" 
                                            @click="deleteComment(comment.id)"
                                            class="text-red-400 hover:text-red-600 transition-colors">
                                        🗑️
                                    </button>
                                </div>

                                <div class="mt-3">
                                    <div class="text-yellow-400 text-xs mb-1">
                                        {{ '⭐'.repeat(comment.rating) }}
                                    </div>
                                    <p class="text-gray-700 leading-relaxed">{{ comment.content }}</p>
                                </div>
                            </div>

                            <div v-if="pig.comments.length === 0" class="text-center py-10 text-gray-400">
                                No hay comentarios todavía. ¡Sé el primero en opinar!
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
