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
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-950 p-8 rounded-3xl shadow-2xl border-b-8 border-yellow-500">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-200 text-[10px] font-black uppercase tracking-widest mb-2 border border-yellow-500/20">
                        Tu Selección
                    </span>
                    <h2 class="font-black text-4xl text-white leading-none tracking-tighter">
                        Mi Carrito
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Productos seleccionados de la chacra.</p>
                </div>
                <div class="text-left md:text-right border-l-4 md:border-l-0 md:border-r-4 border-yellow-500 pl-4 md:pl-0 md:pr-4">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total Items</span>
                    <p class="text-3xl font-black text-yellow-400 leading-none">{{ cartItemsCount }} <span class="text-xs text-yellow-600">productos</span></p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-100/50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
                
                <!-- Header del carrito -->
                <div class="bg-white rounded-[2rem] overflow-hidden shadow-sm border border-gray-100 p-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div class="bg-gray-950 text-white w-16 h-16 rounded-2xl flex items-center justify-center text-3xl">🛒</div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-950 tracking-tighter leading-none">TUS PRODUCTOS</h3>
                                <p class="text-yellow-600 font-bold text-xs uppercase tracking-widest mt-1">
                                    {{ cartItemsCount }} producto{{ cartItemsCount !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>
                        <Link href="/" class="bg-yellow-500 text-gray-950 px-6 py-3 rounded-xl font-black hover:bg-gray-950 hover:text-yellow-400 transition-all">
                            Seguir Comprando
                        </Link>
                    </div>
                </div>

                <!-- Carrito vacío -->
                <div v-if="!cart || Object.keys(cart).length === 0" class="text-center py-20">
                    <div class="text-6xl mb-4">🛒</div>
                    <h3 class="text-2xl font-black text-gray-950 mb-2">Tu carrito está vacío</h3>
                    <p class="text-gray-600 mb-8">Agrega algunos productos del mercado de Yacus</p>
                    <Link href="/" class="bg-yellow-500 text-gray-950 px-8 py-3 rounded-xl font-black hover:bg-gray-950 hover:text-yellow-400 transition-all">
                        Ir al Mercado 🛍️
                    </Link>
                </div>

                <!-- Items del carrito -->
                <div v-else class="space-y-6">
                    <div v-for="(item, id) in cart" :key="id" 
                         class="group bg-white rounded-[2rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100">
                        
                        <div class="p-8 flex gap-6">
                            <!-- Imagen del producto -->
                            <div class="w-32 h-32 bg-gray-100 rounded-2xl overflow-hidden flex-shrink-0 relative">
                                <img v-if="item.product?.images?.[0]" 
                                     :src="getSafeProductImageSrc(item.product.images[0].image_path)" 
                                     class="w-full h-full object-cover"
                                     @error="(e) => { e.target.removeAttribute('src'); e.target.src = FALLBACK_IMAGE; }" />
                                <div v-else class="w-full h-full bg-gray-50 flex items-center justify-center text-gray-300">
                                    <span class="text-4xl">📷</span>
                                </div>
                            </div>
                            
                            <!-- Información del producto -->
                            <div class="flex-1">
                                <h3 class="text-xl font-black text-gray-950 mb-2">{{ item.product.name }}</h3>
                                <p class="text-xs text-gray-400 mb-4 font-semibold italic">
                                    📍 {{ item.product.seller?.name || 'Comunidad Yacus' }}
                                </p>
                                
                                <!-- Controles de cantidad -->
                                <div class="flex items-center gap-4 mb-4">
                                    <span class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Cantidad</span>
                                    <div class="flex items-center gap-2">
                                        <button @click="updateQuantity(id, item.quantity - 1)" 
                                                class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-300 transition-colors">
                                            <span class="text-sm font-bold">−</span>
                                        </button>
                                        <span class="w-12 text-center font-black text-gray-950">{{ item.quantity }}</span>
                                        <button @click="updateQuantity(id, item.quantity + 1)" 
                                                class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center hover:bg-gray-950 hover:text-yellow-400 transition-colors">
                                            <span class="text-sm font-bold">+</span>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Precio y eliminar -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Subtotal</span>
                                        <p class="text-2xl font-black text-gray-950">S/ {{ (item.product.price * item.quantity).toFixed(2) }}</p>
                                    </div>
                                    <button @click="removeFromCart(id)" 
                                            class="bg-red-500 text-white px-4 py-2 rounded-xl text-sm font-black hover:bg-red-600 transition-all">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total y Checkout -->
                <div v-if="cart && Object.keys(cart).length > 0" class="bg-white rounded-[2rem] overflow-hidden shadow-sm border border-gray-100 p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Total General</span>
                            <p class="text-3xl font-black text-gray-950">S/ {{ cartTotal.toFixed(2) }}</p>
                        </div>
                        <Link href="/checkout" class="bg-yellow-500 text-gray-950 px-8 py-4 rounded-xl font-black hover:bg-gray-950 hover:text-yellow-400 transition-all shadow-lg">
                            Proceder al Pago 💳
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