<script setup>
import { ref, onMounted } from 'vue';

const slides = ref([
    {
        id: 1,
        image: 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=1920&h=600&fit=crop&crop=entropy&auto=format',
        title: 'Cuyes de Alta Calidad',
        subtitle: 'Los mejores reproductores para tu chacra',
        cta: 'Ver Productos',
        link: '/products'
    },
    {
        id: 2,
        image: 'https://images.unsplash.com/photo-1586201375795-eb5a233474e5?w=1920&h=600&fit=crop&crop=entropy&auto=format',
        title: 'Carne Beneficiada',
        subtitle: 'Lista para consumo con certificación sanitaria',
        cta: 'Comprar Ahora',
        link: '/products'
    },
    {
        id: 3,
        image: 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=1920&h=600&fit=crop&crop=entropy&auto=format',
        title: 'Insumos y Accesorios',
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
    <div class="relative w-full h-96 md:h-[500px] overflow-hidden bg-gradient-to-br from-amber-50 to-orange-50">
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
                    @error="$event.target.src='/storage/slider/default.jpg'"
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
                            class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg"
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
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all duration-300 z-30 group"
        >
            <svg class="w-6 h-6 group-hover:-translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
        
        <button 
            @click="nextSlide"
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all duration-300 z-30 group"
        >
            <svg class="w-6 h-6 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 18l6-6-6-6"/>
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

        <!-- Auto-play Toggle -->
        <button 
            @click="toggleAutoPlay"
            class="absolute top-4 right-4 bg-white/80 hover:bg-white text-gray-800 p-2 rounded-full shadow-lg transition-all duration-300 z-30"
            :title="isAutoPlaying ? 'Pausar' : 'Reproducir'"
        >
            <svg v-if="isAutoPlaying" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="6" y="4" width="4" height="16"/>
                <rect x="14" y="4" width="4" height="16"/>
            </svg>
            <svg v-else class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 3l14 9-14 9V3z"/>
            </svg>
        </button>
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
