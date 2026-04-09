<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
 guineaPigs: Array,
 categories: Array,
 selectedCategory: Object,
 events: Array,
 categoryId: String
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
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-950 p-8 rounded-3xl shadow-2xl border-b-8 border-yellow-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-yellow-500/20">
                        {{ selectedCategory ? 'Categoría Seleccionada' : 'Todos los Productos' }}
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        {{ selectedCategory ? selectedCategory.name : 'Catálogo Completo' }}
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">
                        {{ selectedCategory 
                            ? `${selectedCategory.guinea_pigs_count || 0} productos disponibles` 
                            : 'Explora toda nuestra selección de la región' }}
                    </p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-yellow-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Altitud Actual</span>
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 text-yellow-500 transition-transform duration-1000 ease-in-out" 
                             :style="{ transform: `translateY(${-currentAltitude/1000}px)` }" 
                             viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 18l4-6 4 6H7zm10-8l-3-4-3 4h6zm-8-4l-2-3-2 3h4z"/>
                        </svg>
                        <p class="text-3xl font-black text-yellow-400 leading-none">
                            {{ currentAltitude }} <span class="text-xs text-yellow-600">m s. n. m.</span>
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
                
                <!-- Filtro de Categorías -->
                <section class="relative p-6 bg-white rounded-[3rem] shadow-xl border border-gray-100 overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-black text-gray-950 italic tracking-tighter">Filtrar por Categoría</h3>
                            <button 
                                v-if="categoryId"
                                @click="filterByCategory(null)"
                                class="text-sm text-gray-600 hover:text-gray-900 font-semibold">
                                × Limpiar filtro
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button 
                                v-for="category in categories" 
                                :key="category.id"
                                @click="filterByCategory(category.id)"
                                :class="[
                                    'px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-300 border',
                                    categoryId == category.id 
                                        ? 'bg-yellow-500 text-gray-950 border-yellow-400 shadow-lg' 
                                        : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-yellow-50 hover:border-yellow-300 hover:text-yellow-700'
                                ]">
                                {{ category.name }}
                                <span class="ml-1 text-xs opacity-75">({{ category.guinea_pigs_count || 0 }})</span>
                            </button>
                        </div>
                    </div>
                </section>
                
                <!-- Productos -->
                <div v-if="guineaPigs.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                    <div v-for="pig in guineaPigs" :key="pig.id" 
                         class="group bg-white rounded-[2rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100 flex flex-col transform hover:-translate-y-2 hover:border-yellow-100 relative">
                        
                        <div class="absolute inset-0 opacity-[0.03] pointer-events-none rounded-[2rem]" 
                             style="background-image: url('data:image/svg+xml,%3Csvg width=%2740%27 height=%2740%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cdefs%3E%3Cpattern id=%27paper%27 patternUnits=%27userSpaceOnUse%27 width=%274%27 height=%274%27%3E%3Cpath d=%27M0 0h4v4H0z%27 fill=%27%238B4513%27/%3E%3Cpath d=%27M0 2h4v2H0z%27 fill=%27%23D2691E%27/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=%2740%27 height=%2740%27 fill=%27url(%23paper)%27/%3E%3C/svg%3E'); background-size: 40px 40px;">
                        </div>
                        
                        <div @click="router.visit('/product/' + pig.id)" class="relative cursor-pointer overflow-hidden aspect-[4/3]">
                            <div class="absolute top-4 left-4 z-10">
                                <div class="bg-yellow-500/90 backdrop-blur-sm text-gray-950 text-[10px] font-black px-3 py-1 rounded-lg shadow-lg flex items-center gap-1 border border-yellow-400">
                                    <span>**</span>
                                    DIRECTO DE YACUS
                                </div>
                            </div>
                            
                            <template v-if="pig.images && pig.images.length > 0">
                                <img :src="getSafeProductImageSrc(pig)" 
                                     class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                                     @error="(e) => { e.target.removeAttribute('src'); e.target.src = FALLBACK_IMAGE; }" />
                            </template>
                            <div v-else class="w-full h-full bg-indigo-50 flex flex-col items-center justify-center text-indigo-200 border-b-4 border-yellow-500">
                                <span class="text-5xl mb-2">**</span>
                                <p class="text-[9px] font-black uppercase tracking-widest text-indigo-300">Esencia Visual</p>
                            </div>
                        </div>

                        <div class="p-8 flex flex-col flex-1 bg-white">
                            <h2 class="text-xl font-black text-gray-950 mb-1 capitalize group-hover:text-yellow-600 transition-colors tracking-tight">{{ pig.name }}</h2>
                            <p class="text-xs text-gray-400 mb-6 font-semibold italic flex items-center gap-1">
                                <span class="text-green-500">**</span>
                                {{ getProductLocation(pig) }}  {{ pig.seller?.name || 'Comunidad Yacus' }}
                                <span class="text-green-500 ml-1">**</span>
                            </p>
                            
                            <div class="grid grid-cols-2 gap-3 mb-8">
                                <div v-for="(attr, index) in pig.specifications?.slice(0, 2)" :key="index" 
                                     class="bg-gray-50/70 border border-gray-100 p-4 rounded-xl transition-colors group-hover:bg-gray-100/50">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase mb-1 tracking-wider">{{ attr.key }}</p>
                                    <p class="text-xs text-gray-800 font-black truncate">{{ attr.value }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-auto pt-5 border-t border-gray-100">
                                <div class="flex flex-col">
                                    <span class="text-[9px] text-gray-400 font-black uppercase tracking-tighter leading-none">Inversión</span>
                                    <span class="text-3xl font-black text-gray-950">S/ {{ pig.price }}</span>
                                    
                                    <p v-if="pig.stock <= 5 && pig.stock > 0" class="text-[10px] text-red-500 font-bold animate-pulse mt-1">
                                        ** ¡Solo quedan {{ pig.stock }} disponibles!
                                    </p>
                                </div>
                                
                                <button @click.stop="addToCart(pig)" 
                                        :disabled="pig.stock <= 0" 
                                        :class="[
                                            'bg-yellow-500 text-gray-950 w-14 h-14 rounded-2xl flex items-center justify-center transition-all shadow-lg hover:shadow-yellow-100 active:scale-90',
                                            cartAnimating ? 'animate-bounce bg-gray-950 text-yellow-400' : 'hover:bg-gray-950 hover:text-yellow-400 disabled:bg-gray-200'
                                        ]">
                                    <span v-if="pig.stock > 0" class="text-2xl">**</span>
                                    <span v-else class="text-[10px] font-extrabold text-gray-400">FIN</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay productos -->
                <div v-else class="text-center py-20 bg-white rounded-[3rem] shadow-xl border border-gray-100">
                    <div class="text-6xl mb-4">**</div>
                    <h3 class="text-2xl font-black text-gray-950 mb-2">No hay productos disponibles</h3>
                    <p class="text-gray-600 mb-6">
                        {{ selectedCategory 
                            ? `No encontramos productos en la categoría "${selectedCategory.name}"` 
                            : 'No hay productos activos en este momento' }}
                    </p>
                    <button 
                        v-if="categoryId"
                        @click="filterByCategory(null)"
                        class="bg-yellow-500 text-gray-950 px-6 py-3 rounded-xl font-black hover:bg-gray-950 hover:text-yellow-400 transition-colors">
                        Ver todos los productos
                    </button>
                </div>

                <!-- Footer -->
                <footer class="mt-20 text-center pb-12 border-t border-gray-100 pt-12">
                    <div class="flex justify-center gap-2.5 mb-6">
                        <span class="w-3 h-3 rounded-full bg-red-600 shadow-md"></span>
                        <span class="w-3 h-3 rounded-full bg-white border border-gray-200"></span>
                        <span class="w-3 h-3 rounded-full bg-red-600 shadow-md"></span>
                    </div>
                    <p class="text-[11px] text-gray-400 font-black uppercase tracking-widest leading-none">
                        Mundo Yacus  25 Años  Huánuco  Perú
                    </p>
                </footer>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
