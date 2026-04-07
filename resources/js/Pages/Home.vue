<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
 guineaPigs: Array,
 events: Array
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

// 🏔️ Altitud Dinámica según producto (consistente usando ID como semilla)
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
    
    // 🎯 Usar ID como semilla para altitud consistente (convertido a número)
    const seed = Number(pig?.id) || 1;
    const variation = Math.floor((seed * 7) % 200) - 100; // Variación basada en ID
    return baseAltitude + variation;
};

// 📍 Ubicación del Productor (basada en ID para consistencia)
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
    
    // Usar ID para ubicación consistente
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

// 🛒 Estado de animación del carrito
const cartAnimating = ref(false);

function addToCart(pig) {
    if (!page.props.auth.user) {
        router.visit('/login');
        return;
    }
    if (pig.stock <= 0) {
        alert('❌ Este producto está agotado. No hay stock disponible.');
        return;
    }
    
    // 🎯 Activar animación de rebote
    cartAnimating.value = true;
    setTimeout(() => cartAnimating.value = false, 600);
    
    router.post('/cart/add/' + pig.id, {}, {
        onSuccess: (page) => {
            // Mostrar mensaje de éxito o error del backend
            if (page.props.flash?.success) {
                alert(page.props.flash.success);
            } else if (page.props.flash?.error) {
                alert(page.props.flash.error);
            } else {
                alert('¡Agregado al carrito de Mundo Yacus! 🐹🛒');
            }
        },
    });
}
</script>

<template>
    <Head title="Mercado de la Chacra - Mundo Yacus" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-950 p-8 rounded-3xl shadow-2xl border-b-8 border-yellow-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-yellow-500/20">
                        Región de la Nación de los Yacus
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Mercado Directo
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Confluencia de frescura y tradición.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-yellow-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Altitud Actual</span>
                    <div class="flex items-center gap-2">
                        <!-- 🏔️ Icono de Montaña con Efecto Elevación -->
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
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                    <div v-for="pig in guineaPigs" :key="pig.id" 
                         class="group bg-white rounded-[2rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100 flex flex-col transform hover:-translate-y-2 hover:border-yellow-100 relative">
                        
                        <!-- 🎨 Textura artesanal sutil -->
                        <div class="absolute inset-0 opacity-[0.03] pointer-events-none rounded-[2rem]" 
                             style="background-image: url('data:image/svg+xml,%3Csvg width=%2740%27 height=%2740%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cdefs%3E%3Cpattern id=%27paper%27 patternUnits=%27userSpaceOnUse%27 width=%274%27 height=%274%27%3E%3Cpath d=%27M0 0h4v4H0z%27 fill=%27%238B4513%27/%3E%3Cpath d=%27M0 2h4v2H0z%27 fill=%27%23D2691E%27/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=%2740%27 height=%2740%27 fill=%27url(%23paper)%27/%3E%3C/svg%3E'); background-size: 40px 40px;">
                        </div>
                        
                        <!-- Imagen (siempre arriba en móvil, mantiene diseño original en desktop) -->
                        <div @click="router.visit('/product/' + pig.id)" class="relative cursor-pointer overflow-hidden aspect-[4/3]">
                            <!-- 🌟 Sello de Origen Garantizado -->
                            <div class="absolute top-4 left-4 z-10">
                                <div class="bg-yellow-500/90 backdrop-blur-sm text-gray-950 text-[10px] font-black px-3 py-1 rounded-lg shadow-lg flex items-center gap-1 border border-yellow-400">
                                    <span>⭐</span>
                                    DIRECTO DE YACUS
                                </div>
                            </div>
                            
                            <template v-if="pig.images && pig.images.length > 0">
                                <img :src="getSafeProductImageSrc(pig)" 
                                     class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                                     @error="(e) => { e.target.removeAttribute('src'); e.target.src = FALLBACK_IMAGE; }" />
                            </template>
                            <div v-else class="w-full h-full bg-indigo-50 flex flex-col items-center justify-center text-indigo-200 border-b-4 border-yellow-500">
                                <span class="text-5xl mb-2">📸</span>
                                <p class="text-[9px] font-black uppercase tracking-widest text-indigo-300">Esencia Visual</p>
                            </div>
                        </div>

                        <!-- Contenido (diseño original en desktop, optimizado en móvil) -->
                        <div class="p-8 flex flex-col flex-1 bg-white">
                            <h2 class="text-xl font-black text-gray-950 mb-1 capitalize group-hover:text-yellow-600 transition-colors tracking-tight">{{ pig.name }}</h2>
                            <p class="text-xs text-gray-400 mb-6 font-semibold italic flex items-center gap-1">
                                <span class="text-green-500">📍</span>
                                {{ getProductLocation(pig) }} • {{ pig.seller?.name || 'Comunidad Yacus' }}
                                <span class="text-green-500 ml-1">✓</span>
                            </p>
                            
                            <!-- Especificaciones (diseño original en desktop, optimizado en móvil) -->
                            <div class="grid grid-cols-2 gap-3 mb-8">
                                <div v-for="(attr, index) in pig.specifications?.slice(0, 2)" :key="index" 
                                     class="bg-gray-50/70 border border-gray-100 p-4 rounded-xl transition-colors group-hover:bg-gray-100/50">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase mb-1 tracking-wider">{{ attr.key }}</p>
                                    <p class="text-xs text-gray-800 font-black truncate">{{ attr.value }}</p>
                                </div>
                            </div>

                            <!-- Precio y Acción (diseño original en desktop, optimizado en móvil) -->
                            <div class="flex items-center justify-between mt-auto pt-5 border-t border-gray-100">
                                <div class="flex flex-col">
                                    <span class="text-[9px] text-gray-400 font-black uppercase tracking-tighter leading-none">Inversión</span>
                                    <span class="text-3xl font-black text-gray-950">S/ {{ pig.price }}</span>
                                    
                                    <!-- 🔥 Barra de Urgencia (Stock Bajo) -->
                                    <p v-if="pig.stock <= 5 && pig.stock > 0" class="text-[10px] text-red-500 font-bold animate-pulse mt-1">
                                        🔥 ¡Solo quedan {{ pig.stock }} disponibles!
                                    </p>
                                </div>
                                
                                <button @click.stop="addToCart(pig)" 
                                        :disabled="pig.stock <= 0" 
                                        :class="[
                                            'bg-yellow-500 text-gray-950 w-14 h-14 rounded-2xl flex items-center justify-center transition-all shadow-lg hover:shadow-yellow-100 active:scale-90',
                                            cartAnimating ? 'animate-bounce bg-gray-950 text-yellow-400' : 'hover:bg-gray-950 hover:text-yellow-400 disabled:bg-gray-200'
                                        ]">
                                    <span v-if="pig.stock > 0" class="text-2xl">🛒</span>
                                    <span v-else class="text-[10px] font-extrabold text-gray-400">FIN</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <section v-if="events && events.length > 0" class="relative mt-20 p-8 md:p-12 bg-gray-950 rounded-[3rem] shadow-2xl border-t-8 border-yellow-500 overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%274%27 height=%274%27 viewBox=%270 0 4 4%27%3E%3Cpath fill=%27%23FFD700%27 fill-opacity=%270.4%27 d=%27M1 3h1v1H1V3zm2-2h1v1H3V1z%27%3E%3C/path%27%3E%3C/svg%27%3E');"></div>
                    </div>

                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                        <div class="flex items-center gap-4">
                            <span class="text-5xl">📢</span>
                            <div>
                                <h2 class="text-3xl font-black text-white italic tracking-tighter">Voces del Valle Yacus</h2>
                                <p class="text-yellow-200 text-sm">Novedades, ferias y la vida en la comunidad.</p>
                            </div>
                        </div>
                        <Link href="/admin/events" class="text-yellow-400 font-bold text-sm hover:text-yellow-300 bg-yellow-500/10 px-5 py-2.5 rounded-xl border border-yellow-500/20 transition-colors">Ver calendario 🗓️</Link>
                    </div>

                    <div class="relative z-10 flex overflow-x-auto pb-6 gap-6 snap-x scrollbar-thin scrollbar-thumb-yellow-600 scrollbar-track-gray-800">
                        <div v-for="event in events" :key="event.id" 
                             class="min-w-[300px] md:min-w-[340px] bg-gray-900 rounded-3xl p-5 shadow-inner border border-gray-800 group transition-all duration-300 hover:border-yellow-500/30 snap-start">
                            <div class="relative mb-5 overflow-hidden rounded-2xl shadow-lg">
                                <img :src="event.image_url" class="h-48 w-full object-cover rounded-2xl transition-transform duration-700 group-hover:scale-105">
                                <div class="absolute bottom-3 left-3 bg-white/95 backdrop-blur px-3.5 py-1.5 rounded-xl text-[10px] font-black text-gray-950 shadow-md">
                                    {{ event.formatted_date }}
                                </div>
                            </div>
                            <h3 class="font-black text-lg text-white leading-tight group-hover:text-yellow-400 transition-colors mb-4 tracking-tight">{{ event.title }}</h3>
                            <div class="w-12 h-1.5 bg-yellow-500/20 rounded-full group-hover:w-full transition-all duration-500 group-hover:bg-yellow-500"></div>
                        </div>
                    </div>
                </section>
                
                <footer class="mt-20 text-center pb-12 border-t border-gray-100 pt-12">
                    <div class="flex justify-center gap-2.5 mb-6">
                        <span class="w-3 h-3 rounded-full bg-red-600 shadow-md"></span>
                        <span class="w-3 h-3 rounded-full bg-white border border-gray-200"></span>
                        <span class="w-3 h-3 rounded-full bg-red-600 shadow-md"></span>
                    </div>
                    <p class="text-[11px] text-gray-400 font-black uppercase tracking-widest leading-none">
                        Mundo Yacus • 25 Años • Huánuco • Perú
                    </p>
                </footer>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Scrollbar personalizado para los eventos (La Estrechez) */
.scrollbar-thin {
    scrollbar-width: thin;
}
.scrollbar-thumb-yellow-600::-webkit-scrollbar-thumb {
    background-color: #ca8a04;
    border-radius: 20px;
}
.scrollbar-track-gray-800::-webkit-scrollbar-track {
    background-color: #1f2937;
    border-radius: 20px;
}
.snap-x {
    scroll-snap-type: x mandatory;
}
.snap-start {
    scroll-snap-align: start;
}
</style>