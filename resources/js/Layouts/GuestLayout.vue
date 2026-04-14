<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showingLoginDropdown = ref(false);

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- Header Estilo Marketplace -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Izquierda: Logo y Menú de Navegación -->
                    <div class="flex items-center space-x-8">
                        <!-- Logo -->
                        <Link href="/" class="flex items-center">
                            <ApplicationLogo class="h-8 w-auto fill-current text-amber-600" />
                            <span class="ml-2 text-xl font-bold text-gray-900">Mundo Yacus</span>
                        </Link>
                        
                        <!-- Menú de Navegación -->
                        <nav class="hidden md:flex space-x-6">
                            <Link href="/products" class="text-gray-700 hover:text-amber-600 font-medium transition-colors">
                                Productos
                            </Link>
                            <Link href="/categories" class="text-gray-700 hover:text-amber-600 font-medium transition-colors">
                                Categorías
                            </Link>
                            <Link href="/about" class="text-gray-700 hover:text-amber-600 font-medium transition-colors">
                                Nosotros
                            </Link>
                            <Link href="/contact" class="text-gray-700 hover:text-amber-600 font-medium transition-colors">
                                Contacto
                            </Link>
                        </nav>
                    </div>

                    <!-- Derecha: Login con Dropdown Integrado -->
                    <div class="flex items-center space-x-4">
                        <!-- Dropdown Login/Registro -->
                        <div 
                            v-if="canLogin || canRegister"
                            class="relative"
                            @mouseenter="showingLoginDropdown = true"
                            @mouseleave="showingLoginDropdown = false"
                        >
                            <button class="inline-flex items-center px-4 py-2 border border-amber-600 text-amber-600 hover:bg-amber-600 hover:text-white font-medium rounded-lg transition-colors">
                                <span v-if="canLogin">Mi Cuenta</span>
                                <span v-else>Registrarse</span>
                                <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div 
                                v-show="showingLoginDropdown"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                            >
                                <div class="py-2">
                                    <!-- Opción de Login -->
                                    <div v-if="canLogin">
                                        <div class="px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wider">
                                            Acceder
                                        </div>
                                        <Link 
                                            href="/login"
                                            class="block px-4 py-3 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors font-medium"
                                        >
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l7-7-7-7"/>
                                                </svg>
                                                Iniciar Sesión
                                            </div>
                                        </Link>
                                    </div>
                                    
                                    <!-- Opciones de Registro -->
                                    <div v-if="canRegister">
                                        <div class="px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wider">
                                            Nueva Cuenta
                                        </div>
                                        <Link 
                                            href="/register/customer"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors"
                                        >
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                                    <circle cx="8.5" cy="7" r="4"/>
                                                    <path d="M20 8v6M23 11h-6"/>
                                                </svg>
                                                Cliente
                                            </div>
                                        </Link>
                                        <Link 
                                            href="/register/admin"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors"
                                        >
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                                    <circle cx="8.5" cy="7" r="4"/>
                                                    <path d="M20 8v6M23 11h-6"/>
                                                </svg>
                                                Vendedor
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
