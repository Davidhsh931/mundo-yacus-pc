<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    cart: Object,
    suggestedProducts: Array,
    events: Array
});

const page = usePage();

const FALLBACK_IMAGE = '/images/cobaya-fondo-blanco.jpg';

const isInvalidImageValue = (value) => {
    if (value === null || value === undefined) return true;
    if (value === 0 || value === '0') return true;
    if (typeof value === 'string' && value.trim() === '') return true;
    return false;
};

const getProductImage = (imagePath) => {
    if (!imagePath) return FALLBACK_IMAGE;
    if (isInvalidImageValue(imagePath)) return FALLBACK_IMAGE;
    if (imagePath.startsWith('http')) return imagePath;
    let fixedPath = imagePath.startsWith('/') ? imagePath : '/' + imagePath;
    if (!fixedPath.startsWith('/storage/')) {
        fixedPath = '/storage/' + fixedPath.replace(/^\/?storage\/?/, '');
    }
    return fixedPath;
};

const getSafeProductImageSrc = (imagePath) => {
    const src = getProductImage(imagePath);
    return isInvalidImageValue(src) ? null : src;
};

// Función para eliminar del carrito
function removeFromCart(id) {
    router.post('/cart/remove/' + id, {}, {
        onSuccess: () => {
            // Recargar la página para actualizar el carrito
            router.reload();
        }
    });
}

// Función para actualizar cantidad
function updateQuantity(id, quantity) {
    if (quantity < 1) return;
    
    router.patch('/cart/update/' + id, { quantity }, {
        onSuccess: () => {
            router.reload();
        }
    });
}

// Calcular total del carrito
const cartTotal = computed(() => {
    if (!props.cart) return 0;
    return Object.values(props.cart).reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0);
});

// Calcular cantidad total de items
const cartItemsCount = computed(() => {
    if (!props.cart) return 0;
    return Object.values(props.cart).reduce((count, item) => {
        return count + item.quantity;
    }, 0);
});
</script>

<template>
    <Head title="Carrito de Compras - Mundo Yacus" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 px-2">
                <!-- Left: cart info -->
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 tracking-wide mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 inline-block"></span>
                        Tu Selección
                    </span>
                    <h2 class="text-2xl font-medium text-gray-900 leading-tight">Mi Carrito</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Productos seleccionados de la chacra.</p>
                </div>

                <!-- Right: items count -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 text-right shrink-0">
                    <p class="text-[11px] font-medium text-amber-700 tracking-wide">Total Items</p>
                    <p class="text-xl font-medium text-amber-900 leading-tight">
                        {{ cartItemsCount.toLocaleString() }}
                        <span class="text-xs text-amber-600">productos</span>
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">
                
                <!-- Header del carrito -->
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="bg-amber-100 w-12 h-12 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"/>
                                    <circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 leading-tight">Tus Productos</h3>
                                <p class="text-amber-600 text-xs font-medium mt-1">
                                    {{ cartItemsCount }} producto{{ cartItemsCount !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>
                        <Link href="/" class="text-amber-600 border border-amber-200 bg-amber-50 rounded-lg px-4 py-2 text-sm font-medium hover:bg-amber-100 transition-colors">
                            Seguir Comprando
                        </Link>
                    </div>
                </div>

                <!-- Carrito vacío -->
                <div v-if="!cart || Object.keys(cart).length === 0" class="text-center py-16 bg-white border border-gray-100 rounded-2xl shadow-sm">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Tu carrito está vacío</h3>
                    <p class="text-gray-500 text-sm mb-6">Agrega algunos productos del mercado de Yacus</p>
                    <Link href="/" class="text-amber-600 border border-amber-200 bg-amber-50 rounded-lg px-4 py-2 text-sm font-medium hover:bg-amber-100 transition-colors">
                        Ir al Mercado
                    </Link>
                </div>

                <!-- Items del carrito -->
                <div v-else class="space-y-4">
                    <div v-for="(item, id) in cart" :key="id" 
                         class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
                        
                        <div class="p-4 flex gap-4">
                            <!-- Imagen del producto -->
                            <div class="w-24 h-24 bg-gray-50 rounded-xl overflow-hidden flex-shrink-0">
                                <img v-if="item.image" 
                                     :src="getSafeProductImageSrc(item.image)" 
                                     class="w-full h-full object-cover"
                                     @error="(e) => { e.target.removeAttribute('src'); e.target.src = FALLBACK_IMAGE; }" />
                                <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <path d="M21 15l-5-5L5 21"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Información del producto -->
                            <div class="flex-1">
                                <h3 class="text-base font-medium text-gray-900 mb-1">{{ item.name || 'Producto sin nombre' }}</h3>
                                <p class="text-xs text-gray-400 mb-3 flex items-center gap-1">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                        <circle cx="12" cy="9" r="2.5"/>
                                    </svg>
                                    Comunidad Yacus
                                </p>
                                
                                <!-- Controles de cantidad -->
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Cantidad</span>
                                    <div class="flex items-center gap-2">
                                        <button @click="updateQuantity(id, item.quantity - 1)" 
                                                class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center hover:bg-gray-200 transition-colors">
                                            <span class="text-xs font-medium">-</span>
                                        </button>
                                        <span class="w-8 text-center text-sm font-medium">{{ item.quantity }}</span>
                                        <button @click="updateQuantity(id, item.quantity + 1)" 
                                                class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center hover:bg-amber-200 transition-colors">
                                            <span class="text-xs font-medium">+</span>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Precio y eliminar -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Subtotal</span>
                                        <p class="text-lg font-medium text-gray-900 leading-none">
                                            <span class="text-sm text-gray-500">S/ </span>{{ ((item.price || 0) * item.quantity).toFixed(2) }}
                                        </p>
                                    </div>
                                    <button @click="removeFromCart(id)" 
                                            class="text-red-500 hover:text-red-600 transition-colors text-sm font-medium">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total y Checkout -->
                <div v-if="cart && Object.keys(cart).length > 0" class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Total General</span>
                            <p class="text-2xl font-medium text-gray-900 leading-none">
                                <span class="text-sm text-gray-500">S/ </span>{{ cartTotal.toFixed(2) }}
                            </p>
                        </div>
                        <Link href="/checkout" class="bg-amber-600 text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-amber-700 transition-colors">
                            Proceder al Pago
                        </Link>
                    </div>
                </div>

                <!-- Carrito Vacío con Sugerencias -->
                <div v-if="!cart || Object.keys(cart).length === 0" class="text-center py-16">
                    <div class="mb-8">
                        <span class="text-6xl">🛒</span>
                    </div>
                    <h3 class="text-2xl font-black text-stone-900 mb-4">Tu carrito está vacío</h3>
                    <p class="text-stone-600 mb-8 max-w-md mx-auto">
                        ¡No te preocupes! Tenemos estos productos que podrían gustarte:
                    </p>
                    
                    <!-- Productos Sugeridos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                        <div v-for="suggested in suggestedProducts" :key="suggested.id" 
                             class="bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all hover:border-amber-500 group">
                            <Link :href="`/product/${suggested.id}`" class="block">
                                <!-- Imagen -->
                                <div class="aspect-square bg-stone-100 relative overflow-hidden">
                                    <img v-if="suggested.images && suggested.images[0]" 
                                         :src="`/storage/${suggested.images[0].image_path}`" 
                                         :alt="suggested.name"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <span class="text-4xl">🐹</span>
                                    </div>
                                    
                                    <!-- Badge de AGOTADO si aplica -->
                                    <div v-if="suggested.stock <= 0" class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-black">
                                        ¡AGOTADO!
                                    </div>
                                </div>
                                
                                <!-- Info -->
                                <div class="p-4">
                                    <h4 class="font-black text-stone-900 mb-2 group-hover:text-amber-600 transition-colors">
                                        {{ suggested.name }}
                                    </h4>
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-black text-amber-600">S/. {{ suggested.price }}</span>
                                        <span v-if="suggested.stock > 0" class="text-xs text-stone-500">
                                            {{ suggested.stock }} disponibles
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                    
                    <!-- Botón para seguir comprando -->
                    <Link href="/" 
                          class="inline-flex items-center gap-2 bg-amber-600 text-white px-8 py-4 rounded-2xl font-black hover:bg-amber-700 transition-colors shadow-lg">
                        <span>🛍️</span>
                        Seguir Explorando
                    </Link>
                </div>

                <!-- Botón de Procesar Pedido -->
                <div v-if="cart && Object.keys(cart).length > 0" class="mt-8">
                    <Link href="/checkout" 
                          class="w-full bg-amber-600 text-white p-6 rounded-2xl font-black text-xl hover:bg-amber-700 transition-colors text-center block shadow-lg">
                        🛒 PROCESAR PEDIDO
                    </Link>
                </div>

                <!-- Events section (si existe) -->
                <section v-if="events && events.length > 0" class="mt-20">
                    <!-- Events content here -->
                </section>
            </div>
        </div>

        <footer class="py-20 bg-stone-950 text-stone-500 border-t-4 border-amber-600">
            <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-12 items-center">
                <div class="text-center md:text-left">
                    <p class="text-2xl font-black text-white italic tracking-tighter leading-none">YACUS</p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-amber-600">Huánuco, Perú</p>
                </div>
                <div class="flex justify-center gap-4 text-4xl opacity-20">
                    🏔️ 🌿 ☀️
                </div>
                <div class="text-center md:text-right">
                    <p class="text-[9px] font-black uppercase tracking-widest text-stone-400">© 2026 DISTRITO DE YACUS</p>
                    <p class="text-[8px] mt-1 italic">Tierra de la Nación de los Yacus</p>
                </div>
            </div>
        </footer>

    </AuthenticatedLayout>
</template>

<style scoped>
.snap-x {
    scroll-snap-type: x mandatory;
    scrollbar-width: thin;
    scrollbar-color: #d97706 #1c1917;
}
.snap-x::-webkit-scrollbar {
    height: 4px;
}
.snap-x::-webkit-scrollbar-thumb {
    background: #d97706;
}
</style>