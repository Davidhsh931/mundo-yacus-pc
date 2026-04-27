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
    <div class="min-h-screen bg-gray-100">
        <!-- Header Estilo Peru Marketplace -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Izquierda: Logo y Menú de Navegación (Zona de exploración) -->
                    <div class="flex items-center space-x-8">
                        <!-- Logo -->
                        <Link href="/" class="flex items-center">
                            <ApplicationLogo class="h-8 w-auto fill-current text-amber-600" />
                            <span class="ml-2 text-xl font-bold text-gray-900">Mundo Yacus</span>
                        </Link>
                        
                        <!-- Login con Dropdown Integrado (Estilo Peru Marketplace) -->
                        <div 
                            v-if="canLogin || canRegister"
                            class="relative"
                        >
                            <button @click="showingLoginDropdown = !showingLoginDropdown" class="inline-flex items-center px-4 py-2 text-gray-700 hover:text-amber-600 font-medium transition-colors border border-gray-300 rounded-lg hover:border-amber-500">
                                <span v-if="canLogin">Iniciar sesión</span>
                                <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <!-- Overlay para Click Away -->
                            <div v-if="showingLoginDropdown" @click="showingLoginDropdown = false" class="fixed inset-0 z-40"></div>
                            
                            <!-- Dropdown Menu (Registro como sub-botón) -->
                            <div 
                                v-show="showingLoginDropdown"
                                class="absolute right-0 mt-1 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-[100]"
                            >
                                <div class="py-2">
                                    <!-- Opción Principal: Iniciar Sesión -->
                                    <div v-if="canLogin">
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
                                    
                                    <!-- Sub-opciones: Registro -->
                                    <div v-if="canRegister">
                                        <div class="border-t border-gray-100 my-2"></div>
                                        <Link 
                                            href="/register"
                                            class="block px-4 py-3 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors"
                                        >
                                            Registrarse
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
        <main class="flex items-center justify-center min-h-[calc(100vh-4rem)]">
            <slot />
        </main>
    </div>
</template>
