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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 px-2">
                <!-- Left: product info -->
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 tracking-wide mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 inline-block"></span>
                        Detalle del Producto
                    </span>
                    <h2 class="text-2xl font-medium text-gray-900 leading-tight">{{ pig.name }}</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Conoce todas las características de este producto.</p>
                </div>

                <!-- Right: availability -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 text-right shrink-0">
                    <p class="text-[11px] font-medium text-amber-700 tracking-wide">Disponibilidad</p>
                    <p class="text-xl font-medium leading-tight" :class="pig.stock > 0 ? 'text-green-700' : 'text-red-700'">
                        {{ pig.stock > 0 ? pig.stock + ' unidades' : 'AGOTADO' }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">
                
                <!-- Producto Principal -->
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                        
                        <!-- Galería de Imágenes -->
                        <div class="p-6 bg-gray-50/50 order-1 lg:order-1">
                            <ProductGallery :images="formattedImages" />
                        </div>

                        <!-- Información del Producto -->
                        <div class="p-6 space-y-6 order-2 lg:order-2">
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
                                
                                <h1 class="text-3xl font-medium text-gray-900 mt-4 tracking-tight">{{ pig.name }}</h1>
                                <p class="text-amber-600 font-medium flex items-center mt-2 text-sm">
                                    <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                        <circle cx="12" cy="9" r="2.5"/>
                                    </svg>
                                    Chacras de Yacus, Perú
                                </p>
                                
                                <!-- Descripción del producto -->
                                <div v-if="pig.description" class="mt-6 p-6 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl border border-yellow-100">
                                    <h3 class="text-xs font-black text-yellow-700 uppercase tracking-[0.2em] mb-3 flex items-center">
                                        <span class="mr-2">📝</span> Descripción del Producto
                                    </h3>
                                    <p class="text-gray-700 leading-relaxed text-lg">{{ pig.description }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100 inline-flex">
                                <span class="text-2xl font-medium text-gray-900">S/ {{ pig.price }}</span>
                                <div class="h-6 w-[1px] bg-gray-300"></div>
                                <span class="text-gray-500 text-sm">IVA incluido</span>
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
                                <div>
                                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide leading-none mb-1">Precio Total</p>
                                    <p class="text-2xl font-medium text-gray-900 leading-none">
                                        <span class="text-sm text-gray-500">S/ </span>{{ pig.price }}
                                    </p>
                                </div>
                                <button @click="addToCart" 
                                        :disabled="pig.stock <= 0" 
                                        class="w-10 h-10 rounded-lg border flex items-center justify-center transition-all duration-200 bg-amber-50 border-amber-200 hover:bg-amber-100 disabled:bg-gray-50 disabled:border-gray-100">
                                    <svg v-if="pig.stock > 0" class="w-4 h-4 stroke-amber-700" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="21" r="1"/>
                                        <circle cx="20" cy="21" r="1"/>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                        <line x1="12" y1="10" x2="12" y2="16"/>
                                        <line x1="9" y1="13" x2="15" y2="13"/>
                                    </svg>
                                    <svg v-else class="w-4 h-4 stroke-gray-300" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Comentarios -->
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6 flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="9" r="2.5"/>
                            </svg>
                            Opiniones de la Comunidad
                        </h3>

                        <!-- Formulario para nuevo comentario -->
                        <div v-if="canComment" class="mb-8 p-4 bg-amber-50 border border-amber-100 rounded-xl">
                            <h4 class="text-sm font-medium text-gray-900 mb-4">Comparte tu experiencia</h4>
                            <form @submit.prevent="submitComment" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tu opinión</label>
                                    <textarea
                                        v-model="form.content"
                                        rows="3"
                                        class="w-full border-gray-200 rounded-lg p-3 text-sm focus:ring-amber-500 focus:border-amber-500"
                                        placeholder="Cuéntanos tu experiencia con este producto..."
                                        required
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Calificación</label>
                                    <div class="flex gap-1">
                                        <button v-for="star in 5" :key="star" type="button" 
                                                @click="form.rating = star"
                                                class="text-xl transition-colors"
                                                :class="star <= form.rating ? 'text-amber-400' : 'text-gray-300'">
                                            **
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" 
                                        :disabled="form.processing"
                                        class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-700 transition disabled:opacity-50">
                                    {{ form.processing ? 'Enviando...' : 'Enviar Opinión' }}
                                </button>
                            </form>
                        </div>

                        <!-- Lista de comentarios -->
                        <div class="space-y-4">
                            <div v-for="comment in pig.comments" :key="comment.id" 
                                 class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-600 text-sm font-medium">
                                            {{ comment.user?.name?.substring(0, 2).toUpperCase() || '??' }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ comment.user?.name }}</p>
                                            <p class="text-xs text-gray-400">{{ new Date(comment.created_at).toLocaleDateString() }}</p>
                                        </div>
                                    </div>
                                    <button v-if="auth.user?.id === comment.user_id" 
                                            @click="deleteComment(comment.id)"
                                            class="text-gray-400 hover:text-red-500 transition-colors text-sm">
                                        **
                                    </button>
                                </div>

                                <div class="mt-2">
                                    <div class="text-amber-400 text-xs mb-1">
                                        {{ '**'.repeat(comment.rating) }}
                                    </div>
                                    <p class="text-gray-700 text-sm leading-relaxed">{{ comment.content }}</p>
                                </div>
                            </div>

                            <div v-if="pig.comments.length === 0" class="text-center py-8 text-gray-400 text-sm">
                                No hay comentarios todavía. ¡Sé el primero en opinar!
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
