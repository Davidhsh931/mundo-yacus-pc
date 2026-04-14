<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

// Contador de productos para el carrito
const cartCount = computed(() => {
    const cart = page.props.cart || {};
    return Object.keys(cart).length;
});

// Sistema de notificaciones desde la base de datos
const notifications = ref([]);

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
    fetch('/admin/notifications/mark-all-read', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
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
    
    // 1. Marcar como leída localmente
    notification.read = true;
    
    // 2. Persistir en backend con fetch (no Inertia)
    fetch(`/admin/notifications/${notification.id}/read`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Notificación marcada como leída en backend:', data);
    })
    .catch(errors => {
        console.error('❌ Error guardando notificación:', errors);
        notification.read = false;
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

        <div class="min-h-screen bg-white">
            <nav class="border-b border-gray-100 bg-white">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex items-center space-x-2">
                                <Link href="/" class="flex items-center">
                                    <ApplicationLogo class="h-8 w-auto fill-current text-amber-600" />
                                    <span class="ml-2 text-xl font-bold text-gray-900">Mundo Yacus</span>
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <!-- ZONA ADMIN: Solo visible para administradores -->
                                <template v-if="$page.props.auth.user?.role === 'admin'">
                                    <NavLink href="/admin/dashboard" :active="$page.component === 'Admin/Dashboard'">
                                        📊 Dashboard Admin
                                    </NavLink>
                                    
                                    <NavLink href="/admin/guinea-pigs" :active="$page.component.startsWith('Admin/GuineaPigs')" class="bg-green-50 text-green-700">
                                        🐹 Gestión de Productos
                                    </NavLink>
                                    
                                    <NavLink href="/admin/orders" :active="route().current('admin.orders.*')" class="bg-purple-50 text-purple-700">
                                        🏭 Gestión de Ventas
                                    </NavLink>
                                    
                                    <div class="border-l border-gray-300 h-6 mx-2"></div>
                                </template>
                                
                                <!-- ZONA CLIENTE: Visible para todos -->
                                <NavLink href="/" :active="$page.component === 'Home'">
                                    🛒 Tienda
                                </NavLink>
                                
                                <NavLink href="/cart" :active="$page.component === 'Cart'" class="relative">
                                    🛒 Carrito
                                    <span v-if="cartCount > 0" class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                        {{ cartCount }}
                                    </span>
                                </NavLink>
                                
                                <!-- Notificaciones Admin -->
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
                                                            🔄
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

                                <NavLink href="/orders" :active="$page.component === 'Orders'">
                                    📦 Mis Pedidos
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <div class="relative ms-3">
                                <Dropdown v-if="$page.props.auth.user" align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                                                {{ $page.props.auth.user.name }}
                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <!-- Opciones de Cliente -->
                                        <DropdownLink href="/profile"> 👤 Perfil </DropdownLink>
                                        <DropdownLink href="/orders"> 📦 Mis Pedidos </DropdownLink>
                                        
                                        <!-- Separador y Opciones de Admin -->
                                        <template v-if="$page.props.auth.user?.role === 'admin'">
                                            <div class="border-t border-gray-100"></div>
                                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                🏭 Administración
                                            </div>
                                            <DropdownLink href="/admin/dashboard"> 📊 Dashboard Admin </DropdownLink>
                                            <DropdownLink href="/admin/orders"> 💰 Gestión de Ventas </DropdownLink>
                                            <DropdownLink href="/admin/guinea-pigs"> 🐹 Gestión de Productos </DropdownLink>
                                        </template>
                                        
                                        <!-- Salir -->
                                        <div class="border-t border-gray-100"></div>
                                        <DropdownLink href="/logout" method="post" as="button"> 🚪 Salir </DropdownLink>
                                    </template>
                                </Dropdown>

                                <div v-else class="space-x-4">
                                    <Link href="/login" class="text-sm text-gray-700 hover:text-gray-900 font-medium">Login</Link>
                                    <Link href="/register" class="text-sm text-gray-700 hover:text-gray-900 font-medium">Registro</Link>
                                </div>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
                    <!-- ZONA CLIENTE: Visible para todos -->
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink href="/"> 🛒 Tienda </ResponsiveNavLink>
                        <ResponsiveNavLink href="/cart"> 🛒 Carrito </ResponsiveNavLink>
                        <ResponsiveNavLink href="/orders"> 📦 Mis Pedidos </ResponsiveNavLink>
                    </div>

                    <!-- ZONA ADMIN: Solo para administradores -->
                    <template v-if="$page.props.auth.user?.role === 'admin'">
                        <div class="border-t border-gray-200"></div>
                        <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            🏭 Administración
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
            </nav>

            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>