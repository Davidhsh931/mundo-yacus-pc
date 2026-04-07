<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <Head title="Panel Administrativo - Mundo Yacus" />

        <!-- Navigation -->
        <nav class="bg-white shadow-lg border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <Link href="/dashboard" class="flex items-center gap-2">
                                <span class="text-2xl">🐹</span>
                                <span class="font-bold text-xl text-gray-800">Mundo Yacus</span>
                                <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full font-semibold">Admin</span>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link href="/dashboard" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                📊 Dashboard
                            </Link>
                            <Link href="/admin/products" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                🐹 Productos
                            </Link>
                            <Link href="/admin/orders" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                📦 Pedidos
                            </Link>
                            <Link href="/admin/settings" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                ⚙️ Configuración
                            </Link>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center">
                        <!-- Admin Badge -->
                        <div class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium mr-4">
                            👑 Administrador
                        </div>

                        <!-- User Dropdown -->
                        <div class="relative">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=admin&background=6366f1&color=fff" alt="Admin">
                            </button>

                            <!-- Dropdown -->
                            <div v-show="showingNavigationDropdown" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <Link href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        👤 Perfil
                                    </Link>
                                    <Link href="/orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        🛒 Mis Pedidos (Vista Cliente)
                                    </Link>
                                    <hr class="my-1">
                                    <form method="POST" action="/logout">
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            🚪 Cerrar Sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white shadow" v-if="$slots.header">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
