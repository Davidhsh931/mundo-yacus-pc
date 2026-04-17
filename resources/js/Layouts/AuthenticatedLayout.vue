<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array
});

// Usar categorías globales del middleware
const categories = computed(() => page.props.categories || props.categories);

// Depuración de categorías
console.log('📋 Categories recibidas en AuthenticatedLayout:', props.categories);

const showingNavigationDropdown = ref(false);
const showingLoginDropdown = ref(false);
const showingProfileDropdown = ref(false);
const page = usePage();

// Contador de productos para el carrito (usar prop compartida del middleware)
const cartCount = computed(() => page.props.cartCount || 0);

// Sistema de notificaciones desde la base de datos
const notifications = ref([]);

// Sistema de búsqueda global
const searchQuery = ref(new URLSearchParams(window.location.search).get('search') || '');

const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.visit(`/products?search=${encodeURIComponent(searchQuery.value)}`);
    }
};

// Función para forzar recarga de productos del servidor
const refreshProducts = () => {
    console.log('🔄 Forzando recarga de productos...');
    
    // Opción 1: Recargar solo props con cache buster (recomendado)
    router.reload({ 
        only: ['guineaPigs', 'cacheBuster'],
        onSuccess: () => {
            console.log('✅ Productos recargados con cache buster');
        }
    });
    
    // Opción 2: Forzar reload completa (más drástico)
    // window.location.reload();
};

// Cargar notificaciones desde el backend
const loadNotifications = async () => {
    try {
        const response = await fetch('/admin/notifications', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            }
        });
        const data = await response.json();
        notifications.value = data;
        console.log('✅ Notificaciones cargadas:', data);
    } catch (error) {
        console.error('Error cargando notificaciones:', error);
        // Usar datos de ejemplo si falla
        notifications.value = [
            { 
                id: 1, 
                message: '📦 Nuevo pedido #123 recibido', 
                type: 'order',
                order_id: 123,
                read: false, 
                created_at: new Date() 
            },
            { 
                id: 2, 
                message: '⚠️ Stock bajo en: Cuy Premium', 
                type: 'warning',
                product_id: 1,
                read: false, 
                created_at: new Date() 
            },
            { 
                id: 3, 
                message: '💬 Nuevo comentario en producto', 
                type: 'comment',
                product_id: 1,
                comment_id: 15,
                read: false, 
                created_at: new Date() 
            },
            { 
                id: 4, 
                message: '🧪 PRUEBA: Clic navegar a producto', 
                type: 'test',
                product_id: 1,
                read: false, 
                created_at: new Date() 
            },
            { 
                id: 5, 
                message: 'ℹ️ Nueva actualización del sistema', 
                type: 'info',
                read: false, 
                created_at: new Date() 
            }
        ];
    }
};

// Cargar notificaciones al montar el componente
onMounted(() => {
    loadNotifications();
});

// Watcher para detectar cambios en cacheBuster
watch(() => page.props.cacheBuster, (newValue, oldValue) => {
    if (newValue !== oldValue) {
        console.log('🔄 Cache buster detectado, recargando productos...');
        // Forzar recarga de la página para actualizar productos
        window.location.reload();
    }
});

const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read).length;
});

// Funciones de notificaciones
const clearAll = () => {
    // Marcar todas como leídas localmente
    notifications.value = notifications.value.map(n => ({ ...n, read: true }));

    // Persistir en backend con fetch (no Inertia)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('/admin/notifications/mark-all-read', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Todas las notificaciones marcadas como leídas:', data);
    })
    .catch(errors => {
        console.error('❌ Error marcando todas como leídas:', errors);
    });
};

const markAsRead = (notification) => {
    // DEBUG: Ver qué datos tiene la notificación
    console.log('🔔 Notificación clickeada:', notification);
    console.log('📋 Tipo:', notification.type);
    console.log('📋 Order ID:', notification.order_id);
    console.log('📋 Product ID:', notification.product_id);
    console.log('📋 Comment ID:', notification.comment_id);
    console.log('🔢 Contador ANTES de marcar:', unreadCount.value);

    // 1. Marcar como leída localmente (forzar reactividad)
    const index = notifications.value.findIndex(n => n.id === notification.id);
    if (index !== -1) {
        // Usar splice para forzar reactividad de Vue
        notifications.value.splice(index, 1, { ...notifications.value[index], read: true });
        console.log('✅ Notificación marcada como leída en índice:', index);
        console.log('🔢 Contador DESPUÉS de marcar:', unreadCount.value);
    } else {
        console.error('❌ No se encontró la notificación en el array');
    }

    // 2. Persistir en backend con fetch (no Inertia)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch(`/admin/notifications/${notification.id}/read`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Notificación marcada como leída en backend:', data);
    })
    .catch(errors => {
        console.error('❌ Error guardando notificación:', errors);
        // Revertir si falla
        if (index !== -1) {
            notifications.value[index].read = false;
        }
    });

    // 3. Navegación inteligente según tipo de notificación
    console.log('🧭 Iniciando navegación...');

    if (notification.type === 'order' && notification.order_id) {
        console.log('🚀 Navegando a pedido:', notification.order_id);
        router.visit(`/admin/orders/${notification.order_id}`);
    } else if (notification.type === 'warning' && notification.product_id) {
        console.log('🚀 Navegando a EDITAR producto:', notification.product_id);
        router.visit(`/admin/guinea-pigs/${notification.product_id}/edit`);
    } else if (notification.type === 'comment' && notification.product_id) {
        console.log('🚀 Navegando a comentario:', notification.product_id);
        router.visit(`/product/${notification.product_id}#comment-${notification.comment_id}`);
    } else if (notification.type === 'test' && notification.product_id) {
        console.log('🧪 PRUEBA: Navegando a producto:', notification.product_id);
        router.visit(`/product/${notification.product_id}`);
    } else if (notification.type === 'info') {
        console.log('ℹ️ INFO: Abriendo panel de configuración');
        router.visit('/admin/settings');
    } else {
        console.warn('⚠️ No se pudo navegar: tipo o IDs faltantes', {
            type: notification.type,
            order_id: notification.order_id,
            product_id: notification.product_id
        });
    }
};

const getNotificationColor = (type) => {
    const colors = {
        'order': 'bg-blue-100 text-blue-800',
        'warning': 'bg-yellow-100 text-yellow-800',
        'comment': 'bg-green-100 text-green-800',
        'test': 'bg-purple-100 text-purple-800',
        'info': 'bg-gray-100 text-gray-800'
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};

const getNotificationIcon = (type) => {
    const icons = {
        'order': '📦',
        'warning': '⚠️',
        'comment': '💬',
        'test': '🧪',
        'info': 'ℹ️'
    };
    return icons[type] || '📢';
};

const formatNotificationTime = (date) => {
    return new Date(date).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <div>
        <!-- Banner de Anuncios Urgentes -->
        <div v-if="$page.props.settings?.banner_active === '1' && $page.props.settings?.banner_text"
             class="bg-red-600 text-white py-2 px-4 text-center text-sm font-black animate-pulse uppercase tracking-tight">
            ⚠️ {{ $page.props.settings?.banner_text }}
        </div>

        <!-- Barra Superior de Accesos (Separada) -->
        <div class="bg-gray-50 border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-end items-center py-2 space-x-4">
                    <!-- Usuario NO logueado -->
                    <div v-if="!$page.props.auth.user" class="relative">
                        <button @click="showingLoginDropdown = !showingLoginDropdown" 
                                class="text-xs text-gray-600 hover:text-amber-600 transition-colors flex items-center">
                            Iniciar sesión
                            <svg class="w-3 h-3 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <!-- Overlay para Click Away -->
                        <div v-if="showingLoginDropdown" 
                             @click="showingLoginDropdown = false" 
                             class="fixed inset-0 z-40"></div>
                        
                        <!-- Dropdown Menu -->
                        <div v-show="showingLoginDropdown" 
                             class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-[100]">
                            <div class="py-2">
                                <!-- Opción Principal: Iniciar Sesión -->
                                <Link href="/login"
                                      class="block px-4 py-3 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors font-medium">
                                    Iniciar Sesión
                                </Link>

                                <!-- Sub-opción: Registrarse -->
                                <div class="border-t border-gray-100 my-2">
                                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Registrarse como
                                    </div>
                                    <Link href="/register/comprador"
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                        🛒 Comprador
                                    </Link>
                                    <Link href="/register/vendedor"
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                        🏪 Vendedor
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Usuario SÍ logueado -->
                    <div v-else class="relative">
                        <button @click="showingProfileDropdown = !showingProfileDropdown" 
                                class="text-xs text-gray-700 hover:text-amber-600 transition-colors flex items-center">
                            <span class="text-xs text-gray-700">
                                Hola, {{ $page.props.auth.user.name }}
                            </span>
                            <svg class="w-3 h-3 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <!-- Overlay para Click Away -->
                        <div v-if="showingProfileDropdown" 
                             @click="showingProfileDropdown = false" 
                             class="fixed inset-0 z-40"></div>
                        
                        <!-- Dropdown Menu -->
                        <div v-show="showingProfileDropdown" 
                             class="absolute right-0 mt-1 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-[100]">
                            <div class="py-2">
                                <!-- Opciones para Admin -->
                                <div v-if="$page.props.auth.user?.role === 'admin'">
                                    <!-- Panel de Administración con sub-opciones -->
                                    <div class="px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wider">
                                        Panel de Administración
                                    </div>
                                    <Link href="/admin/dashboard" 
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                        Dashboard
                                    </Link>
                                    <Link href="/admin/guinea-pigs" 
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                        Productos
                                    </Link>
                                    <Link href="/admin/orders" 
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                        Pedidos
                                    </Link>
                                    <Link href="/admin/ai-training" 
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                        IA Training
                                    </Link>
                                    <Link href="/admin/events"
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                        Eventos
                                    </Link>
                                    <Link href="/admin/users"
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                        Usuarios
                                    </Link>

                                    <div class="border-t border-gray-100 my-2"></div>
                                </div>
                                
                                <!-- Opciones comunes -->
                                <Link href="/profile" 
                                      class="block px-4 py-3 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors font-medium">
                                    Mi Perfil
                                </Link>
                                
                                <Link href="/orders" 
                                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                    Mis Pedidos
                                </Link>
                                
                                <!-- Configuración según rol -->
                                <Link v-if="$page.props.auth.user?.role === 'admin'" 
                                      href="/admin/settings" 
                                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                    Configuración
                                </Link>
                                <Link v-else 
                                      href="/settings" 
                                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                    Configuración
                                </Link>
                                
                                <div class="border-t border-gray-100 my-2"></div>
                                
                                <!-- Salir -->
                                <Link href="/logout" 
                                      method="post" 
                                      as="button"
                                      class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    Salir
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-white">
            <!-- Header Simplificado con Solo Elementos Funcionales -->
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Izquierda: Logo y Categorías -->
                        <div class="flex items-center space-x-8">
                            <Link href="/" class="flex items-center">
                                <ApplicationLogo class="h-8 w-auto fill-current text-amber-600" />
                                <span class="ml-2 text-xl font-bold text-gray-900">Mundo Yacus</span>
                            </Link>

                            <!-- Menú de Categorías con Dropdown -->
                            <div class="relative group hidden md:block">
                                <button class="text-gray-700 hover:text-amber-600 font-medium transition-colors flex items-center">
                                    Categoria
                                    <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <!-- Dropdown de Categorías -->
                                <div class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="py-2">
                                        <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            Selecciona la categoría
                                        </div>
                                        <div v-for="category in categories" :key="category.id">
                                            <Link :href="'/products?category=' + category.id"
                                                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                                🏷️ {{ category.name }} ({{ category.guinea_pigs_count || 0 }})
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Centro: Búsqueda -->
                        <div class="flex-1 max-w-md mx-4 hidden md:block">
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Buscar productos..."
                                    class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
                                    @keyup.enter="performSearch"
                                />
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Derecha: Solo Elementos Funcionales -->
                        <div class="flex items-center space-x-4">
                            <!-- Notificaciones Admin (Funcional) -->
                            <div v-if="$page.props.auth.user?.role === 'admin'" class="relative">
                                <button @click="showingNavigationDropdown = !showingNavigationDropdown" 
                                        class="p-2 text-gray-600 hover:text-gray-900 relative">
                                    <span class="text-lg">🔔</span>
                                    <span v-if="unreadCount > 0" 
                                          class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center">
                                        {{ unreadCount > 9 ? '9+' : unreadCount }}
                                    </span>
                                </button>
                                
                                <!-- Overlay para Click Away -->
                                <div v-if="showingNavigationDropdown" 
                                     @click="showingNavigationDropdown = false" 
                                     class="fixed inset-0 z-40"></div>
                                
                                <!-- Dropdown de Notificaciones -->
                                <div v-show="showingNavigationDropdown" 
                                     class="absolute right-0 z-50 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200">
                                    <div class="py-2">
                                        <div class="px-4 py-2 border-b border-gray-200">
                                            <div class="flex justify-between items-center">
                                                <h3 class="text-sm font-black text-gray-900">Notificaciones</h3>
                                                <div class="flex gap-2">
                                                    <button @click="clearAll" 
                                                            class="text-xs text-red-600 hover:text-red-800">
                                                        Limpiar todo
                                                    </button>
                                                    <button @click="refreshProducts" 
                                                            class="text-xs text-blue-600 hover:text-blue-800"
                                                            title="Refrescar productos">
                                                        🔔
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div v-if="notifications.length === 0" class="px-4 py-4 text-center text-gray-500 text-sm">
                                            No hay notificaciones nuevas
                                        </div>
                                        
                                        <div v-else class="max-h-64 overflow-y-auto">
                                            <div v-for="notification in notifications.slice(0, 10)" :key="notification.id"
                                                 @click="markAsRead(notification)"
                                                 :class="['px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100', 
                                                          notification.read ? 'bg-gray-50 opacity-60' : 'bg-white']">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm"
                                                         :class="getNotificationColor(notification.type)">
                                                        {{ getNotificationIcon(notification.type) }}
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm text-gray-900">{{ notification.message }}</p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            {{ formatNotificationTime(notification.created_at) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Carrito (Funcional) -->
                            <Link href="/cart" class="relative p-2 text-gray-600 hover:text-amber-600 transition-colors">
                                <span class="text-lg">🛒</span>
                                <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                    {{ cartCount }}
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Mobile menu -->
            <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
                <!-- ZONA CLIENTE: Visible para todos -->
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink href="/"> 🛒 Tienda </ResponsiveNavLink>
                    <ResponsiveNavLink href="/products"> 🏷️ Selecciona la categoría </ResponsiveNavLink>
                    <div v-for="category in categories" :key="category.id">
                        <ResponsiveNavLink :href="'/products?category=' + category.id"> 
                            🏷️ {{ category.name }} ({{ category.guinea_pigs_count || 0 }})
                        </ResponsiveNavLink>
                    </div>
                    <ResponsiveNavLink href="/cart"> 🛒 Carrito </ResponsiveNavLink>
                    <ResponsiveNavLink href="/orders"> 📦 Mis Pedidos </ResponsiveNavLink>
                </div>

                <!-- ZONA ADMIN: Solo para administradores -->
                <template v-if="$page.props.auth.user?.role === 'admin'">
                    <div class="border-t border-gray-200"></div>
                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        🏢 Administración
                    </div>
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink href="/dashboard"> 📊 Dashboard </ResponsiveNavLink>
                        <ResponsiveNavLink href="/admin/orders"> 💰 Gestión de Ventas </ResponsiveNavLink>
                        <ResponsiveNavLink href="/admin/guinea-pigs"> 🐹 Gestión de Productos </ResponsiveNavLink>
                    </div>
                </template>

                <div class="border-t border-gray-200 pb-1 pt-4">
                    <div v-if="$page.props.auth.user" class="px-4 space-y-2">
                        <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <form method="POST" action="/logout">
                                <button type="submit" class="block w-full text-left text-base font-medium text-red-600 hover:text-red-700">
                                    🚪 Salir
                                </button>
                            </form>
                        </div>
                    </div>
                    <div v-else class="px-4 space-y-2">
                        <Link href="/login" class="block text-base font-medium text-gray-600">Login</Link>
                        <Link href="/register" class="block text-base font-medium text-gray-600">Registro</Link>
                    </div>
                </div>
            </div>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>