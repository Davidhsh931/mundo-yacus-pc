<template>
    <div class="image-cropper-container">
        <!-- Input para seleccionar archivo -->
        <input 
            type="file" 
            ref="fileInput"
            @change="onFileSelect"
            class="hidden"
            accept="image/*"
        />
        
        <!-- Botón para seleccionar imagen -->
        <button 
            type="button"
            @click="selectFile"
            class="w-full px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white font-black rounded-xl transition-colors flex items-center justify-center gap-2"
        >
            <span>📷</span>
            {{ hasImage ? 'Cambiar Imagen' : 'Seleccionar Imagen' }}
        </button>
        
        <!-- Modal de recorte -->
        <div v-if="showCropper" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4">
            <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <!-- Header del modal -->
                <div class="bg-amber-500 text-white p-4 flex justify-between items-center">
                    <h3 class="text-lg font-black">📷 Recortar Imagen del Producto</h3>
                    <button 
                        @click="cancelCrop"
                        class="text-white hover:text-amber-200 transition-colors"
                    >
                        ❌
                    </button>
                </div>
                
                <!-- Área de recorte simple -->
                <div class="p-4 max-h-[60vh] overflow-auto">
                    <div class="cropper-wrapper bg-gray-100 rounded-lg">
                        <img 
                            ref="cropperImage"
                            :src="imageSrc" 
                            class="max-w-full max-h-[400px] mx-auto cursor-move"
                            :style="cropStyle"
                            @mousedown="startDrag"
                        />
                    </div>
                    
                    <!-- Controles simples -->
                    <div class="flex gap-2 mt-4 justify-center">
                        <button 
                            @click="rotate(-90)"
                            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium transition-colors"
                        >
                            ↪️ Rotar -90°
                        </button>
                        <button 
                            @click="rotate(90)"
                            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium transition-colors"
                        >
                            ↩️ Rotar +90°
                        </button>
                        <button 
                            @click="zoom(0.1)"
                            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium transition-colors"
                        >
                            🔍+ Zoom
                        </button>
                        <button 
                            @click="zoom(-0.1)"
                            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium transition-colors"
                        >
                            🔍- Zoom
                        </button>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="p-4 bg-gray-50 border-t">
                    <div class="flex gap-3">
                        <button 
                            @click="cancelCrop"
                            class="flex-1 px-4 py-3 bg-gray-300 hover:bg-gray-400 text-gray-800 font-black rounded-xl transition-colors"
                        >
                            ❌ Cancelar
                        </button>
                        <button 
                            @click="cropImage"
                            class="flex-1 px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white font-black rounded-xl transition-colors"
                        >
                            ✅ Recortar y Usar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Preview de la imagen recortada -->
        <div v-if="croppedImage" class="mt-4">
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-sm font-bold text-gray-700 mb-2">📸 Imagen recortada:</p>
                <div class="w-32 h-32 mx-auto bg-gray-200 rounded-lg overflow-hidden border-2 border-gray-300">
                    <img :src="croppedImage" class="w-full h-full object-cover" />
                </div>
                <p class="text-xs text-gray-500 text-center mt-2">Lista para usar</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

// Props
const props = defineProps({
    modelValue: {
        type: File,
        default: null
    }
});

// Emits
const emit = defineEmits(['update:modelValue']);

// Refs
const fileInput = ref(null);
const cropperImage = ref(null);
const imageSrc = ref('');
const showCropper = ref(false);
const croppedImage = ref('');
const hasImage = ref(false);

// Estado del cropper
const rotation = ref(0);
const scale = ref(1);

// Methods
const selectFile = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

const onFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validar tipo de archivo
        if (!file.type.startsWith('image/')) {
            alert('❌ Por favor selecciona un archivo de imagen válido.');
            return;
        }
        
        // Validar tamaño (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('❌ La imagen no puede pesar más de 5MB.');
            return;
        }
        
        // Crear preview y abrir modal
        const reader = new FileReader();
        reader.onload = (e) => {
            imageSrc.value = e.target.result;
            showCropper.value = true;
            rotation.value = 0;
            scale.value = 1;
        };
        reader.readAsDataURL(file);
    }
};

const cropStyle = ref({
    transform: 'rotate(0deg) scale(1)'
});

const rotate = (degree) => {
    rotation.value += degree;
    updateTransform();
};

const zoom = (delta) => {
    scale.value = Math.max(0.5, Math.min(3, scale.value + delta));
    updateTransform();
};

const updateTransform = () => {
    cropStyle.value.transform = `rotate(${rotation.value}deg) scale(${scale.value})`;
};

const cropImage = () => {
    // Crear canvas para recortar
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    img.onload = () => {
        // Tamaño objetivo (cuadrado)
        const size = 800;
        canvas.width = size;
        canvas.height = size;
        
        // Calcular dimensiones para recorte cuadrado
        const imgSize = Math.min(img.width, img.height);
        const sx = (img.width - imgSize) / 2;
        const sy = (img.height - imgSize) / 2;
        
        // Aplicar transformaciones y dibujar
        ctx.save();
        ctx.translate(size/2, size/2);
        ctx.rotate(rotation.value * Math.PI / 180);
        ctx.scale(scale.value, scale.value);
        ctx.drawImage(img, sx - imgSize/2, sy - imgSize/2, imgSize, imgSize);
        ctx.restore();
        
        // Convertir a blob y crear archivo
        canvas.toBlob((blob) => {
            const file = new File([blob], `cropped_${Date.now()}.jpg`, {
                type: 'image/jpeg',
                lastModified: Date.now()
            });
            
            // Crear preview
            croppedImage.value = URL.createObjectURL(blob);
            hasImage.value = true;
            
            // Emitir el archivo recortado
            emit('update:modelValue', file);
            
            // Cerrar modal
            showCropper.value = false;
            
            // Limpiar input
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        }, 'image/jpeg', 0.9);
    };
    
    img.src = imageSrc.value;
};

const cancelCrop = () => {
    showCropper.value = false;
    imageSrc.value = '';
    rotation.value = 0;
    scale.value = 1;
    
    // Limpiar input
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Exponer métodos para uso externo
defineExpose({
    clearImage: () => {
        croppedImage.value = '';
        hasImage.value = false;
        emit('update:modelValue', null);
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
});
</script>

<style scoped>
.image-cropper-container {
    width: 100%;
}

.cropper-wrapper {
    max-height: 400px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: repeating-conic-gradient(#f3f4f6 0% 25%, white 0% 50%) 50% / 20px 20px;
}
</style>
