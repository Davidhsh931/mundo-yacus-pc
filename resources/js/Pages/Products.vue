<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    guineaPigs: Array,
    categories: Array,
    selectedCategory: Object,
    events: Array,
    categoryId: String,
    search: String,
    minPrice: String,
    maxPrice: String,
    sortBy: String,
    sortOrder: String
});

const page = usePage();

// "Cerebro" de imágenes mejorado
const FALLBACK_IMAGE = '/images/cobaya-fondo-blanco.jpg';

const isInvalidImageValue = (value) => {
    if (value === null || value === undefined) return true;
    if (value === 0 || value === '0') return true;
    if (typeof value === 'string' && value.trim() === '') return true;
    return false;
};

// Altitud Dinámica según producto
const getProductAltitude = (pig) => {
    const altitudes = {
        'Mejorada': 3226,
        'Andina': 3850,
        'Criolla': 2800,
        'Peruana': 3400,
        'Inti': 3600,
        'Inca': 3900
    };
    
    const breed = pig?.breed || 'Mejorada';
    const baseAltitude = altitudes[breed] || 3226;
    
    const seed = Number(pig?.id) || 1;
    const variation = Math.floor((seed * 7) % 200) - 100;
    return baseAltitude + variation;
};

const getProductLocation = (pig) => {
    const locations = [
        'Comunidad San Cristóbal',
        'Valle Colorado',
        'Chacra La Esperanza',
        'Comunidad Santa Rosa',
        'Valle del Yacus',
        'Comunidad San Miguel',
        'Chacra El Progreso',
        'Valle Andino'
    ];

    const index = (pig?.id || 1) % locations.length;
    return locations[index];
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('es-ES', options);
};

const currentAltitude = computed(() => {
    if (props.guineaPigs.length > 0) {
        return getProductAltitude(props.guineaPigs[0]);
    }
    return 3226;
});

const getProductImage = (pig) => {
    if (!pig?.images?.length) return FALLBACK_IMAGE;
    const path = pig.images[0]?.image_path;
    if (isInvalidImageValue(path)) return FALLBACK_IMAGE;
    if (path.startsWith('http')) return path;
    let fixedPath = path.startsWith('/') ? path : '/' + path;
    if (!fixedPath.startsWith('/storage/')) {
        fixedPath = '/storage/' + fixedPath.replace(/^\/?storage\/?/, '');
    }
    return fixedPath;
};

const getSafeProductImageSrc = (pig) => {
    const src = getProductImage(pig);
    return isInvalidImageValue(src) ? null : src;
};

// Estado de animación del carrito
const cartAnimating = ref(false);

// Variables reactivas para búsqueda y filtros
const searchQuery = ref(props.search || '');
const minPrice = ref(props.minPrice || '');
const maxPrice = ref(props.maxPrice || '');
const sortBy = ref(props.sortBy || 'created_at');
const sortOrder = ref(props.sortOrder || 'desc');

// Función para aplicar búsqueda y filtros
function applyFilters() {
    const params = new URLSearchParams();

    if (props.categoryId) {
        params.append('category', props.categoryId);
    }
    if (searchQuery.value) {
        params.append('search', searchQuery.value);
    }
    if (minPrice.value) {
        params.append('min_price', minPrice.value);
    }
    if (maxPrice.value) {
        params.append('max_price', maxPrice.value);
    }
    if (sortBy.value) {
        params.append('sort', sortBy.value);
    }
    if (sortOrder.value) {
        params.append('order', sortOrder.value);
    }

    const queryString = params.toString();
    const url = queryString ? `/products?${queryString}` : '/products';
    router.visit(url);
}

function addToCart(pig) {
    if (!page.props.auth.user) {
        router.visit('/login');
        return;
    }
    if (pig.stock <= 0) {
        alert('Este producto está agotado. No hay stock disponible.');
        return;
    }
    
    cartAnimating.value = true;
    setTimeout(() => cartAnimating.value = false, 600);

    router.post('/cart/add/' + pig.id, {}, {
        onSuccess: (page) => {
            if (page.props.flash?.success) {
                alert(page.props.flash.success);
            } else if (page.props.flash?.error) {
                alert(page.props.flash.error);
            } else {
                alert('¡Agregado al carrito de Mundo Yacus! ');
            }
        },
    });
}

function filterByCategory(categoryId) {
    const url = categoryId ? `/products?category=${categoryId}` : '/products';
    router.visit(url);
}
</script>

<template>
    <Head :title="selectedCategory ? `${selectedCategory.name} - Mundo Yacus` : 'Todos los Productos - Mundo Yacus'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 px-2">
                <!-- Left: identity -->
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 tracking-wide mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 inline-block"></span>
                        {{ selectedCategory ? 'Categoría Seleccionada' : 'Todos los Productos' }}
                    </span>
                    <h2 class="text-2xl font-medium text-gray-900 leading-tight">
                        {{ selectedCategory ? selectedCategory.name : 'Catálogo Completo' }}
                    </h2>
                    <p class="text-sm text-gray-400 mt-0.5">
                        {{ selectedCategory
                            ? `${selectedCategory.guinea_pigs_count || 0} productos disponibles`
                            : 'Explora toda nuestra selección de la región' }}
                    </p>
                </div>

                <!-- Right: altitude -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 text-right shrink-0">
                    <p class="text-[11px] font-medium text-amber-700 tracking-wide">Altitud actual</p>
                    <p class="text-xl font-medium text-amber-900 leading-tight">
                        {{ currentAltitude.toLocaleString() }}
                        <span class="text-xs text-amber-600">m s. n. m.</span>
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">

                <!-- Búsqueda y Filtros -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Precio mínimo -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Precio mínimo (S/)</label>
                            <input
                                v-model="minPrice"
                                type="number"
                                placeholder="0"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- Precio máximo -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Precio máximo (S/)</label>
                            <input
                                v-model="maxPrice"
                                type="number"
                                placeholder="999"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- Ordenamiento -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Ordenar por</label>
                            <select
                                v-model="sortBy"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            >
                                <option value="created_at">Fecha de publicación</option>
                                <option value="price">Precio</option>
                                <option value="name">Nombre</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <!-- Orden -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Orden</label>
                            <select
                                v-model="sortOrder"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            >
                                <option value="desc">Descendente</option>
                                <option value="asc">Ascendente</option>
                            </select>
                        </div>

                        <!-- Botón aplicar filtros -->
                        <div class="flex items-end">
                            <button
                                @click="applyFilters"
                                class="w-full bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-700 transition-colors"
                            >
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Section heading -->
                <div class="flex justify-between items-baseline px-4 sm:px-0">
                    <span class="text-xs text-gray-400">{{ guineaPigs.length }} producto{{ guineaPigs.length !== 1 ? 's' : '' }}</span>
                </div>

                <!-- Mensaje cuando no hay productos en la categoría -->
                <div v-if="!guineaPigs || guineaPigs.length === 0" class="py-20 bg-gray-50 min-h-screen">
                    <div class="text-center">
                        <div class="text-6xl mb-4">🏷️</div>
                        <h2 class="text-2xl font-medium text-gray-900 mb-2">No hay productos en esta categoría</h2>
                        <p class="text-gray-500 mb-6">No se encontraron productos que coincidan con esta categoría.</p>
                    </div>
                </div>

                <!-- Product grid - Diseño Home.vue -->
                <div v-if="guineaPigs && guineaPigs.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="pig in guineaPigs"
                        :key="pig.id"
                        class="group bg-white border border-gray-100 rounded-2xl overflow-hidden flex flex-col transition-all duration-200 hover:border-gray-200 hover:-translate-y-0.5 hover:shadow-sm"
                        :class="{ 'opacity-60': pig.stock <= 0 }"
                    >
                        <!-- Image -->
                        <div
                            @click="router.visit('/product/' + pig.id)"
                            class="relative cursor-pointer overflow-hidden"
                            style="aspect-ratio: 4/3"
                        >
                            <template v-if="pig.images && pig.images.length > 0">
                                <img
                                    :src="getSafeProductImageSrc(pig)"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    @error="(e) => { e.target.removeAttribute('src'); e.target.src = FALLBACK_IMAGE; }"
                                />
                            </template>
                            <div v-else class="w-full h-full bg-amber-50 flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-amber-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path d="M21 15l-5-5L5 21"/>
                                </svg>
                                <p class="text-[10px] font-medium text-amber-300 tracking-wide uppercase">Sin imagen</p>
                            </div>

                            <!-- Origin badge -->
                            <div class="absolute top-2.5 left-2.5">
                                <span class="bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-medium px-2 py-0.5 rounded-md">
                                    Directo de Yacus
                                </span>
                            </div>

                            <!-- Low stock badge -->
                            <div v-if="pig.stock <= 5 && pig.stock > 0" class="absolute top-2.5 right-2.5">
                                <span class="bg-red-50 border border-red-200 text-red-700 text-[10px] font-medium px-2 py-0.5 rounded-md">
                                    Solo {{ pig.stock }} disponibles
                                </span>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="p-4 flex flex-col flex-1 gap-3">
                            <!-- Name & location -->
                            <div>
                                <h3 class="text-[15px] font-medium text-gray-900 capitalize leading-snug group-hover:text-amber-700 transition-colors">
                                    {{ pig.name }}
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                        <circle cx="12" cy="9" r="2.5"/>
                                    </svg>
                                    {{ getProductLocation(pig) }}
                                    <span class="text-gray-200">·</span>
                                    {{ pig.seller?.name || 'Comunidad Yacus' }}
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    Publicado: {{ formatDate(pig.created_at) }}
                                </p>
                            </div>

                            <!-- Specs -->
                            <div class="grid grid-cols-2 gap-1.5">
                                <div
                                    v-for="(attr, index) in pig.specifications?.slice(0, 2)"
                                    :key="index"
                                    class="bg-gray-50 rounded-lg px-2.5 py-2"
                                >
                                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">{{ attr.key }}</p>
                                    <p class="text-xs font-medium text-gray-800 truncate">{{ attr.value }}</p>
                                </div>
                            </div>

                            <!-- Price & CTA -->
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 mt-auto">
                                <div>
                                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide leading-none mb-1">Precio</p>
                                    <p class="text-xl font-medium text-gray-900 leading-none">
                                        <span class="text-sm text-gray-500">S/ </span>{{ pig.price }}
                                    </p>
                                </div>

                                <button
                                    v-if="pig.stock > 0"
                                    @click.stop="addToCart(pig)"
                                    :class="[
                                        'w-9 h-9 rounded-lg border flex items-center justify-center transition-all duration-200',
                                        cartAnimating
                                            ? 'bg-gray-900 border-gray-900'
                                            : 'bg-amber-50 border-amber-200 hover:bg-amber-100'
                                    ]"
                                    title="Agregar al carrito"
                                >
                                    <svg
                                        class="w-4 h-4 transition-colors"
                                        :class="cartAnimating ? 'stroke-amber-400' : 'stroke-amber-700'"
                                        viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                    >
                                        <circle cx="9" cy="21" r="1"/>
                                        <circle cx="20" cy="21" r="1"/>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                        <line x1="12" y1="10" x2="12" y2="16"/>
                                        <line x1="9" y1="13" x2="15" y2="13"/>
                                    </svg>
                                </button>

                                <div v-else class="w-9 h-9 rounded-lg border border-gray-100 bg-gray-50 flex items-center justify-center" title="Sin stock">
                                    <svg class="w-4 h-4 stroke-gray-300" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay productos -->
                <div v-else class="text-center py-20 bg-white rounded-[2rem] shadow-sm border border-gray-100">
                    <div class="text-6xl mb-4">📦</div>
                    <h3 class="text-2xl font-medium text-gray-900 mb-2">No hay productos disponibles</h3>
                    <p class="text-gray-600 mb-6">
                        {{ selectedCategory 
                            ? `No encontramos productos en la categoría "${selectedCategory.name}"` 
                            : 'No hay productos activos en este momento' }}
                    </p>
                    <button 
                        v-if="categoryId"
                        @click="filterByCategory(null)"
                        class="bg-amber-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-amber-700 transition-colors">
                        Ver todos los productos
                    </button>
                </div>

                <!-- Events -->
                <section v-if="events && events.length > 0" class="mt-8"> <div class="flex items-center justify-between mb-4 px-4 sm:px-0">
        <h2 class="text-base font-bold text-gray-800 tracking-tight italic">Voces del valle</h2>
    </div>

    <div class="flex gap-4 overflow-x-auto pb-6 px-4 sm:px-0 snap-x scrollbar-none">
        <div
            v-for="event in events"
            :key="event.id"
            class="snap-start w-[280px] sm:w-[300px] bg-white border border-gray-100 rounded-3xl overflow-hidden flex-shrink-0 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
        >
            <div class="overflow-hidden"> <img
                    :src="event.image_url"
                    class="w-full aspect-video object-cover group-hover:scale-105 transition-transform duration-500"
                />
            </div>
            
            <div class="p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-block text-[10px] font-bold uppercase tracking-widest text-amber-700 bg-amber-50 border border-amber-100 rounded-md px-2 py-1">
                        {{ event.formatted_date }}
                    </span>
                </div>
                <h3 class="text-sm font-semibold text-gray-800 leading-snug group-hover:text-blue-600 transition-colors">
                    {{ event.title }}
                </h3>
            </div>
        </div>
    </div>
</section>

                <!-- Footer -->
                <footer class="pt-8 border-t border-gray-100 text-center pb-4">
                    <div class="flex justify-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span class="w-2 h-2 rounded-full bg-gray-200"></span>
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    </div>
                    <p class="text-[11px] text-gray-400 tracking-widest uppercase">
                        Mundo Yacus · 25 años · Huánuco · Perú
                    </p>
                </footer>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
