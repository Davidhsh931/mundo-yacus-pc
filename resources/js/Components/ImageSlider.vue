<script setup>
import { ref, onMounted } from 'vue';

const slides = ref([
    {
        id: 1,
        image: 'https://i.postimg.cc/tRx0FJfH/Cuy-publicidad.png',
        title: 'Cuyes de Alta Calidad',
        subtitle: 'Los mejores reproductores para tu chacra',
        cta: 'Ver Productos',
        link: '/products'
    },
    {
        id: 2,
        image: 'https://i.postimg.cc/LskKg882/carne-publicidad.jpg',
        title: 'Carne Beneficiada',
        subtitle: 'Lista para consumo con certificación sanitaria',
        cta: 'Comprar Ahora',
        link: '/products'
    },
    {
        id: 3,
        image: 'https://i.postimg.cc/J0PnWQkK/Productos-agricolas-y-mas-publicacion.webp',
        title: 'Productos Agrícolas y más',
        subtitle: 'Todo lo que necesitas para tu producción',
        cta: 'Explorar',
        link: '/products'
    }
]);

const currentSlide = ref(0);
const isAutoPlaying = ref(true);

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % slides.value.length;
};

const prevSlide = () => {
    currentSlide.value = currentSlide.value === 0 ? slides.value.length - 1 : currentSlide.value - 1;
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

const toggleAutoPlay = () => {
    isAutoPlaying.value = !isAutoPlaying.value;
};

onMounted(() => {
    setInterval(() => {
        if (isAutoPlaying.value) {
            nextSlide();
        }
    }, 5000);
});
</script>

<template>
    <div class="relative group w-full h-96 md:h-[500px] overflow-hidden bg-gradient-to-br from-red-50 to-orange-50">
        <!-- Slides -->
        <div class="relative w-full h-full">
            <div 
                v-for="(slide, index) in slides" 
                :key="slide.id"
                class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                :class="{ 'opacity-100': index === currentSlide, 'opacity-0': index !== currentSlide }"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-black/30 z-10"></div>
                <img 
                    :src="slide.image" 
                    :alt="slide.title"
                    class="w-full h-full object-cover"
                    @error="$event.target.style.display='none'"
                />
                
                <!-- Content Overlay -->
                <div class="absolute inset-0 flex items-center justify-center z-20">
                    <div class="text-center text-white px-6 max-w-4xl">
                        <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">
                            {{ slide.title }}
                        </h1>
                        <p class="text-lg md:text-xl mb-8 drop-shadow-md max-w-2xl mx-auto">
                            {{ slide.subtitle }}
                        </p>
                        <a 
                            :href="slide.link"
                            class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg"
                        >
                            {{ slide.cta }}
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Arrows -->
        <button 
            @click="prevSlide"
            class="absolute left-2 top-1/2 -translate-y-1/2 z-40 w-10 h-10 bg-white/90 rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 hover:bg-white hover:scale-110"
        >
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        
        <button 
            @click="nextSlide"
            class="absolute right-2 top-1/2 -translate-y-1/2 z-40 w-10 h-10 bg-white/90 rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 hover:bg-white hover:scale-110"
        >
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <!-- Slide Indicators -->
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-30">
            <button 
                v-for="(slide, index) in slides" 
                :key="slide.id"
                @click="goToSlide(index)"
                class="w-3 h-3 rounded-full transition-all duration-300"
                :class="index === currentSlide ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/70'"
            />
        </div>

    </div>
</template>

<style scoped>
/* Custom animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.slide-enter-active {
    animation: slideIn 0.5s ease-out;
}
</style>
