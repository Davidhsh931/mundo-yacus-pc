<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ImageSlider from '@/Components/ImageSlider.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
    featuredProducts: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head title="Mundo Yacus - Marketplace de Cuyes" />
    
    <!-- Hero Slider -->
    <ImageSlider />
    
    <!-- Sección de Categorías -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Categorías Populares</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Explora nuestras categorías principales y encuentra exactamente lo que necesitas para tu producción
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Categoría Animales -->
                <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow p-8 text-center group">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-green-200 transition-colors">
                        <span class="text-3xl"></span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Animales Vivos</h3>
                    <p class="text-gray-600 mb-4">Cuyes reproductores de alta calidad para cría</p>
                    <Link href="/products?category=animales" class="inline-flex items-center text-amber-600 hover:text-amber-700 font-medium">
                        Explorar categoría
                        <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </div>
                
                <!-- Categoría Carne -->
                <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow p-8 text-center group">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-red-200 transition-colors">
                        <span class="text-3xl"> </span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Carne Beneficiada</h3>
                    <p class="text-gray-600 mb-4">Carne lista para consumo con certificación</p>
                    <Link href="/products?category=carne" class="inline-flex items-center text-amber-600 hover:text-amber-700 font-medium">
                        Explorar categoría
                        <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </div>
                
                <!-- Categoría Insumos -->
                <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow p-8 text-center group">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-200 transition-colors">
                        <span class="text-3xl"> </span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Insumos y Accesorios</h3>
                    <p class="text-gray-600 mb-4">Alimentos, jaulas y herramientas para producción</p>
                    <Link href="/products?category=insumos" class="inline-flex items-center text-amber-600 hover:text-amber-700 font-medium">
                        Explorar categoría
                        <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Productos Destacados -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Productos Destacados</h2>
                    <p class="text-gray-600">Los mejores productos seleccionados por nuestra comunidad</p>
                </div>
                <Link href="/products" class="inline-flex items-center px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition-colors">
                    Ver todos los productos
                    <svg class="w-4 h-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </Link>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Producto Placeholder -->
                <div v-for="i in 4" :key="i" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow group">
                    <div class="aspect-w-16 aspect-h-12 bg-gray-100 relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-amber-50 to-orange-50"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-4xl text-gray-300"> </span>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Producto Destacado {{ i }}</h3>
                        <p class="text-gray-600 text-sm mb-3">Descripción breve del producto</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-amber-600">S/. 150</span>
                            <span class="text-xs text-gray-500 bg-green-100 text-green-800 px-2 py-1 rounded">Disponible</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Sección de Beneficios -->
    <section class="py-16 bg-gradient-to-br from-amber-50 to-orange-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">¿Por qué elegir Mundo Yacus?</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Conectamos directamente a productores con compradores, garantizando calidad y los mejores precios
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Calidad Garantizada</h3>
                    <p class="text-gray-600">Todos nuestros productos pasan por rigurosos controles de calidad</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Entrega Rápida</h3>
                    <p class="text-gray-600">Envíos rápidos y seguros a todo el Perú</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Precios Justos</h3>
                    <p class="text-gray-600">Sin intermediarios, precios directos de productor a consumidor</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h4 class="text-lg font-semibold mb-4">Mundo Yacus</h4>
                    <p class="text-gray-400">El marketplace líder para la comercialización de cuyes y productos derivados en el Perú.</p>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Enlaces Rápidos</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><Link href="/products" class="hover:text-white transition-colors">Productos</Link></li>
                        <li><Link href="/categories" class="hover:text-white transition-colors">Categorías</Link></li>
                        <li><Link href="/about" class="hover:text-white transition-colors">Nosotros</Link></li>
                        <li><Link href="/contact" class="hover:text-white transition-colors">Contacto</Link></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Categorías</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><Link href="/products?category=animales" class="hover:text-white transition-colors">Animales Vivos</Link></li>
                        <li><Link href="/products?category=carne" class="hover:text-white transition-colors">Carne Beneficiada</Link></li>
                        <li><Link href="/products?category=insumos" class="hover:text-white transition-colors">Insumos</Link></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contacto</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>Chacras de Yacus, Perú</li>
                        <li>Teléfono: +51 987 654 321</li>
                        <li>Email: info@mundoyacus.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 Mundo Yacus. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</template>
