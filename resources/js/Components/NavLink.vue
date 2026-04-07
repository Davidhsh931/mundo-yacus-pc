<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    class: {
        type: String,
        default: '',
    },
});

const classes = computed(() => {
    const baseClasses = 'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out';
    
    if (props.active) {
        // Si hay clases personalizadas y son estilos de fondo, úsalas
        if (props.class && (props.class.includes('bg-') || props.class.includes('text-'))) {
            return `${baseClasses} ${props.class}`;
        }
        // Si no, usa los estilos por defecto activo
        return `${baseClasses} border-indigo-400 text-gray-900 focus:border-indigo-700`;
    }
    
    // Si hay clases personalizadas, úsalas en estado inactivo
    if (props.class) {
        return `${baseClasses} border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300 ${props.class}`;
    }
    
    // Estilos por defecto inactivo
    return `${baseClasses} border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300`;
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
