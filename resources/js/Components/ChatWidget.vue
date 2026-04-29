<script setup>
import { ref, nextTick, watch, onMounted } from 'vue';

const isOpen = ref(false);
const message = ref('');
const messages = ref([
    { id: 1, type: 'bot', text: '¡Hola! 👋 Soy el asistente de Mundo Yacus. ¿En qué puedo ayudarte?' }
]);
const isLoading = ref(false);
const messagesContainer = ref(null);
const sessionId = ref(null);
const messageInput = ref(null);
const quickReplies = ref([]);
const buttonAnimations = ref(new Map()); // Para animaciones
const countdowns = ref(new Map()); // Para countdowns
const userPoints = ref(0); // Para gamificación
const isListening = ref(false); // Para reconocimiento de voz
const speechRecognition = ref(null); // API de voz
const isSpeaking = ref(false); // Para síntesis de voz

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const focusInput = async () => {
    await nextTick();
    if (messageInput.value) {
        messageInput.value.focus();
    }
};

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        scrollToBottom();
        focusInput();
    }
};

const sendMessage = async () => {
    if (!message.value.trim() || isLoading.value) return;

    // Validación de mensaje vacío o muy largo
    if (message.value.trim().length === 0) {
        messages.value.push({
            id: Date.now() + 1,
            type: 'bot',
            text: 'Por favor escribe un mensaje antes de enviar.'
        });
        return;
    }

    if (message.value.length > 1000) {
        messages.value.push({
            id: Date.now() + 1,
            type: 'bot',
            text: 'Tu mensaje es demasiado largo. Por favor acorta tu mensaje a menos de 1000 caracteres.'
        });
        return;
    }

    // Add user message
    const userMessage = {
        id: Date.now(),
        type: 'user',
        text: message.value
    };
    messages.value.push(userMessage);
    await scrollToBottom();

    const userText = message.value;
    message.value = '';
    clearQuickRepliesOnSend(); // Limpiar botones al enviar
    isLoading.value = true;

    try {
        const response = await fetch('/api/chat/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ 
                message: userText,
                session_id: sessionId.value 
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Save session ID for future messages
        if (data.session_id) {
            sessionId.value = data.session_id;
        }

        // Add bot response
        messages.value.push({
            id: Date.now() + 1,
            type: 'bot',
            text: data.response,
            intent: data.intent,
            quickReplies: data.quick_replies || []
        });
        quickReplies.value = data.quick_replies || [];
        await scrollToBottom();
        await focusInput(); // Enfocar el input después de recibir respuesta
    } catch (error) {
        console.error('Error sending message:', error);
        
        // Handle different types of errors
        let errorMessage = 'Lo siento, hubo un error. Por favor intenta nuevamente o contáctanos por WhatsApp.';
        
        if (error.message.includes('Unexpected token')) {
            errorMessage = 'Error de conexión con el servidor. Por favor recarga la página e intenta nuevamente.';
        } else if (error.message.includes('HTTP error')) {
            errorMessage = 'Error del servidor. Por favor intenta en unos momentos.';
        }
        
        messages.value.push({
            id: Date.now() + 1,
            type: 'bot',
            text: errorMessage
        });
        await scrollToBottom();
        await focusInput(); // Enfocar el input incluso en caso de error
    } finally {
        isLoading.value = false;
        await focusInput(); // Asegurar que el input esté enfocado al final
    }
};

const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

const clickQuickReply = async (quickReply, messageId) => {
    // Animación del botón
    buttonAnimations.value.set(`${messageId}-${quickReply.text}`, 'clicked');
    
    // Pequeña pausa para la animación
    await new Promise(resolve => setTimeout(resolve, 150));
    
    // Gamificación: sumar puntos por interacción
    if (quickReply.type === 'gamification') {
        userPoints.value += 50;
    } else {
        userPoints.value += 5;
    }
    
    // Iniciar countdown si es oferta
    if (quickReply.type === 'offer' && quickReply.text.includes('⏰')) {
        startCountdown(messageId, quickReply.text);
    }
    
    message.value = quickReply.value;
    sendMessage();
    
    // Limpiar animación después de un tiempo
    setTimeout(() => {
        buttonAnimations.value.delete(`${messageId}-${quickReply.text}`);
    }, 1000);
};

const startCountdown = (messageId, offerText) => {
    const endTime = new Date();
    endTime.setHours(23, 59, 59); // Fin del día
    
    const updateCountdown = () => {
        const now = new Date();
        const diff = endTime - now;
        
        if (diff > 0) {
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            countdowns.value.set(`${messageId}-${offerText}`, `${hours}h ${minutes}m ${seconds}s`);
        } else {
            countdowns.value.delete(`${messageId}-${offerText}`);
        }
    };
    
    updateCountdown();
    const interval = setInterval(updateCountdown, 1000);
    
    // Limpiar intervalo después de 24 horas
    setTimeout(() => clearInterval(interval), 24 * 60 * 60 * 1000);
};

const trackButtonClick = (quickReply, messageType) => {
    // Enviar analytics del clic
    if (sessionId.value) {
        fetch('/api/chat/track-button', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ 
                session_id: sessionId.value,
                button_text: quickReply.text,
                button_value: quickReply.value,
                button_type: quickReply.type,
                message_type: messageType
            }),
        }).catch(console.error);
    }
};

const getButtonStyle = (type) => {
    const styles = {
        'product': 'bg-green-100 hover:bg-green-200 text-green-700 border-green-200 hover:scale-105',
        'info': 'bg-blue-100 hover:bg-blue-200 text-blue-700 border-blue-200 hover:scale-105',
        'shipping': 'bg-purple-100 hover:bg-purple-200 text-purple-700 border-purple-200 hover:scale-105',
        'payment': 'bg-yellow-100 hover:bg-yellow-200 text-yellow-700 border-yellow-200 hover:scale-105',
        'location': 'bg-indigo-100 hover:bg-indigo-200 text-indigo-700 border-indigo-200 hover:scale-105',
        'contact': 'bg-pink-100 hover:bg-pink-200 text-pink-700 border-pink-200 hover:scale-105',
        'action': 'bg-red-100 hover:bg-red-200 text-red-700 border-red-200 hover:scale-105',
        'category': 'bg-orange-100 hover:bg-orange-200 text-orange-700 border-orange-200 hover:scale-105',
        'offer': 'bg-amber-100 hover:bg-amber-200 text-amber-700 border-amber-200 hover:scale-105',
        'multimedia': 'bg-teal-100 hover:bg-teal-200 text-teal-700 border-teal-200 hover:scale-105',
        'comparison': 'bg-cyan-100 hover:bg-cyan-200 text-cyan-700 border-cyan-200 hover:scale-105',
        'service': 'bg-violet-100 hover:bg-violet-200 text-violet-700 border-violet-200 hover:scale-105',
        'gamification': 'bg-rose-100 hover:bg-rose-200 text-rose-700 border-rose-200 hover:scale-105',
        'feedback': 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700 border-emerald-200 hover:scale-105'
    };
    return styles[type] || 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200 hover:scale-105';
};

// Sistema de voz y audio
const initializeSpeechRecognition = () => {
    console.log('🎤 Inicializando sistema de voz...');
    
    // Verificar soporte del navegador
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        console.warn('⚠️ Speech Recognition no soportado en este navegador');
        return;
    }
    
    try {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        speechRecognition.value = new SpeechRecognition();
        
        // Configuración optimizada
        speechRecognition.value.continuous = false;
        speechRecognition.value.interimResults = true;
        speechRecognition.value.lang = 'es-PE';
        speechRecognition.value.maxAlternatives = 1;
        
        // Event handlers mejorados
        speechRecognition.value.onstart = () => {
            console.log('🎤 Comenzando a escuchar...');
            isListening.value = true;
        };
        
        speechRecognition.value.onresult = (event) => {
            console.log('🎤 Resultado recibido:', event);
            let finalTranscript = '';
            let interimTranscript = '';
            
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += transcript + ' ';
                } else {
                    interimTranscript += transcript;
                }
            }
            
            if (finalTranscript) {
                message.value = finalTranscript.trim();
                userPoints.value += 10; // Puntos por usar voz
                console.log('✅ Texto transcritado:', message.value);
                
                // Auto-detener después de obtener resultado
                setTimeout(() => {
                    stopListening();
                }, 1000);
            } else if (interimTranscript) {
                // Mostrar transcripción intermedia (opcional)
                console.log('🔄 Transcripción intermedia:', interimTranscript);
            }
        };
        
        speechRecognition.value.onerror = (event) => {
            console.error('❌ Error en Speech Recognition:', event.error);
            isListening.value = false;
            
            // Manejo específico de errores
            switch (event.error) {
                case 'no-speech':
                    console.log('🔇 No se detectó voz, intenta hablar más claro');
                    break;
                case 'audio-capture':
                    console.log('🎤 No se pudo acceder al micrófono');
                    break;
                case 'not-allowed':
                    console.log('🚫 Permiso de micrófono denegado');
                    break;
                case 'network':
                    console.log('🌐 Error de red, intenta de nuevo');
                    break;
                default:
                    console.log('❌ Error desconocido:', event.error);
            }
        };
        
        speechRecognition.value.onend = () => {
            console.log('🎤 Sesión de reconocimiento terminada');
            isListening.value = false;
        };
        
        console.log('✅ Sistema de voz inicializado correctamente');
        
    } catch (error) {
        console.error('❌ Error al inicializar Speech Recognition:', error);
    }
};

const startListening = () => {
    console.log('🎤 Iniciando escucha...');
    
    if (!speechRecognition.value) {
        console.warn('⚠️ Speech Recognition no inicializado');
        initializeSpeechRecognition();
        return;
    }
    
    if (isListening.value) {
        console.log('🎤 Ya está escuchando');
        return;
    }
    
    try {
        speechRecognition.value.start();
        console.log('🎤 Escuchando...');
    } catch (error) {
        console.error('❌ Error al iniciar reconocimiento:', error);
        isListening.value = false;
    }
};

const stopListening = () => {
    console.log('🎤 Deteniendo escucha...');
    
    if (speechRecognition.value && isListening.value) {
        try {
            speechRecognition.value.stop();
            console.log('🎤 Escucha detenida');
        } catch (error) {
            console.error('❌ Error al detener reconocimiento:', error);
        }
    }
    
    isListening.value = false;
};

const speakText = (text) => {
    console.log('🔊 Hablando texto:', text);
    
    if (!('speechSynthesis' in window)) {
        console.warn('⚠️ Speech Synthesis no soportado');
        return;
    }
    
    if (isSpeaking.value) {
        console.log('🔊 Ya está hablando');
        return;
    }
    
    try {
        // Cancelar cualquier speech anterior
        window.speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'es-PE';
        utterance.rate = 0.9;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;
        
        // Buscar voz española si está disponible
        const voices = window.speechSynthesis.getVoices();
        const spanishVoice = voices.find(voice => 
            voice.lang.includes('es') || voice.lang.includes('PE')
        );
        
        if (spanishVoice) {
            utterance.voice = spanishVoice;
            console.log('🔊 Usando voz española:', spanishVoice.name);
        }
        
        utterance.onstart = () => {
            console.log('🔊 Comenzando a hablar...');
            isSpeaking.value = true;
        };
        
        utterance.onend = () => {
            console.log('🔊 Terminé de hablar');
            isSpeaking.value = false;
        };
        
        utterance.onerror = (event) => {
            console.error('❌ Error en Speech Synthesis:', event);
            isSpeaking.value = false;
        };
        
        window.speechSynthesis.speak(utterance);
        console.log('🔊 Texto enviado a síntesis de voz');
        
    } catch (error) {
        console.error('❌ Error al hablar texto:', error);
        isSpeaking.value = false;
    }
};

const testVoiceSystem = () => {
    console.log('🧪 Iniciando prueba del sistema de voz...');
    
    // 1. Probar síntesis de voz
    speakText("Hola, soy el asistente de Mundo Yacus. El sistema de voz está funcionando correctamente.");
    
    // 2. Mostrar diagnóstico en consola
    setTimeout(() => {
        console.log('📊 Diagnóstico del sistema de voz:');
        console.log('✅ Speech Recognition:', 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window);
        console.log('✅ Speech Synthesis:', 'speechSynthesis' in window);
        console.log('✅ Navegador:', navigator.userAgent);
        console.log('✅ Idioma configurado:', 'es-PE');
        console.log('✅ Sistema inicializado:', !!speechRecognition.value);
        console.log('✅ Estado actual:', isListening.value ? 'Escuchando' : 'Inactivo');
        
        // 3. Probar reconocimiento si está disponible
        if (speechRecognition.value && !isListening.value) {
            console.log('🎤 Iniciando prueba de reconocimiento...');
            startListening();
            
            // Auto-detener después de 3 segundos
            setTimeout(() => {
                if (isListening.value) {
                    stopListening();
                    console.log('🎤 Prueba de reconocimiento completada');
                }
            }, 3000);
        }
    }, 2000);
};

const getButtonAnimation = (messageId, buttonText) => {
    const animation = buttonAnimations.value.get(`${messageId}-${buttonText}`);
    if (animation === 'clicked') {
        return 'scale-95 opacity-80 transform';
    }
    return 'hover:scale-105';
};

// Inicializar sistema de voz al montar el componente
onMounted(() => {
    console.log('🚀 ChatWidget montado, inicializando sistemas...');
    
    // Inicializar sistema de voz
    initializeSpeechRecognition();
    
    // Cargar voces disponibles para síntesis
    if ('speechSynthesis' in window) {
        // Las voces se cargan de forma asíncrona
        const loadVoices = () => {
            const voices = window.speechSynthesis.getVoices();
            console.log('🔊 Voces disponibles:', voices.length);
            const spanishVoices = voices.filter(voice => 
                voice.lang.includes('es') || voice.lang.includes('PE')
            );
            console.log('🔊 Voces españolas:', spanishVoices.length);
        };
        
        // Cargar voces inmediatamente y también cuando se carguen
        loadVoices();
        window.speechSynthesis.onvoiceschanged = loadVoices;
    }
    
    console.log('✅ ChatWidget inicializado completamente');
});

// Auto-scroll when messages change
watch(messages, () => {
    scrollToBottom();
}, { deep: true });

// Limpiar botones cuando el usuario envía un mensaje (no cuando escribe)
const clearQuickRepliesOnSend = () => {
    quickReplies.value = [];
};

// Rate satisfaction
const rateSatisfaction = (rating) => {
    if (sessionId.value) {
        fetch('/api/chat/rate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ 
                session_id: sessionId.value,
                rating: rating 
            }),
        }).catch(console.error);
    }
};
</script>

<template>
    <div class="fixed bottom-4 right-4 z-50">
        <!-- Floating Button -->
        <button
            @click="toggleChat"
            class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center z-40 border-2 border-white"
            :class="{ 'scale-110': isOpen }"
        >
            <svg v-if="!isOpen" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <svg v-else class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Chat Window -->
        <div v-if="isOpen" class="fixed bottom-20 right-4 w-full max-w-sm md:w-96 h-[500px] md:h-[600px] bg-white rounded-lg shadow-2xl flex flex-col z-50 border border-gray-200">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-3 sm:p-4 rounded-t-lg flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white rounded-full flex items-center justify-center">
                        <span class="text-red-600 font-bold text-sm sm:text-base">🐹</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm sm:text-base">Chat Mundo Yacus</h3>
                        <p class="text-xs sm:text-sm opacity-90">Asistente IA Trascendental</p>
                    </div>
                </div>
                <button
                    @click="toggleChat"
                    class="w-6 h-6 sm:w-8 sm:h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition-colors"
                    title="Cerrar chat"
                >
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Messages Container -->
            <div ref="messagesContainer" class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-3 sm:space-y-4 bg-gray-50">
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    class="space-y-2"
                >
                    <div v-if="msg.type === 'user'" class="flex justify-end">
                        <div class="bg-red-600 text-white p-2 sm:p-3 rounded-2xl rounded-tr-md shadow-sm max-w-[70%] sm:max-w-[80%] break-words">
                            <p class="text-sm sm:text-base">{{ msg.text }}</p>
                            <span class="text-xs opacity-75 block mt-1">{{ new Date(msg.timestamp || Date.now()).toLocaleTimeString() }}</span>
                        </div>
                    </div>
                    <div v-else class="flex justify-start">
                        <div class="bg-white text-gray-800 p-2 sm:p-3 rounded-2xl rounded-bl-md shadow-sm max-w-[70%] sm:max-w-[80%] break-words">
                            <p class="text-sm sm:text-base">{{ msg.text }}</p>
                            <!-- Quick Replies -->
                            <div v-if="quickReplies.length > 0" class="mt-2 sm:mt-3 grid grid-cols-2 gap-1 sm:gap-2">
                                <button
                                    v-for="reply in quickReplies"
                                    :key="reply.text"
                                    @click="sendQuickReply(reply.value, reply.type)"
                                    :class="[
                                        'p-1.5 sm:p-2 text-xs sm:text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-all text-gray-700 hover:text-gray-900 border border-gray-200',
                                        getButtonAnimation(msg.id, reply.text)
                                    ]"
                                    :disabled="isLoading"
                                >
                                    <span v-if="buttonAnimations.get(`${msg.id}-${reply.text}`) === 'clicked'" class="inline-block">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            <path d="M9 12l2 2 4-4"/>
                                        </svg>
                                    </span>
                                    <span v-else class="truncate">{{ reply.text }}</span>
                                </button>
                            </div>
                            <!-- Gamificación: Puntos del usuario -->
                            <div v-if="userPoints > 0" class="mt-1 sm:mt-2 text-xs text-gray-500 flex items-center gap-1">
                                <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full font-medium">
                                    🏆 {{ userPoints }} puntos
                                </span>
                                <span v-if="userPoints >= 100" class="text-green-600 hidden sm:inline">🎉 ¡Nivel Avanzado!</span>
                            </div>
                            <!-- Botón de voz para respuesta -->
                            <div v-if="msg.type === 'bot'" class="flex items-center gap-1 sm:gap-2 mt-1">
                                <span class="text-xs text-gray-500 hidden sm:inline">{{ new Date(msg.timestamp || Date.now()).toLocaleTimeString() }}</span>
                                <!-- Botón para escuchar respuesta -->
                                <button
                                    @click="speakText(msg.text)"
                                    :disabled="isSpeaking"
                                    class="p-1 sm:p-1.5 text-gray-400 hover:text-gray-600 transition-colors"
                                    title="Escuchar respuesta"
                                >
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"></path>
                                    </svg>
                                </button>
                                <!-- Indicador de voz activa -->
                                <span v-if="isSpeaking" class="text-xs text-green-600 animate-pulse hidden sm:inline">
                                    🔊 Hablando...
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="isLoading" class="bg-white text-gray-800 p-2 sm:p-3 rounded-2xl rounded-bl-md shadow-sm max-w-[70%] sm:max-w-[80%]">
                    <div class="flex gap-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="p-3 sm:p-4 bg-white border-t border-gray-200">
                <!-- Input con voz -->
                <div class="flex items-center gap-1 sm:gap-2">
                    <input
                        ref="messageInput"
                        v-model="message"
                        @keypress="handleKeyPress"
                        type="text"
                        placeholder="Escribe tu mensaje..."
                        class="flex-1 px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500 touch-action-manipulation"
                        :disabled="isLoading"
                    />
                    
                    <!-- Botón de prueba de voz -->
                    <button
                        @click="testVoiceSystem"
                        class="p-1.5 sm:p-2 bg-purple-500 text-white rounded-full hover:bg-purple-600 transition-all hidden sm:inline-flex"
                        title="Probar sistema de voz"
                    >
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </button>
                    
                    <!-- Botón de voz -->
                    <button
                        @click="isListening ? stopListening() : startListening()"
                        :class="[
                            'p-1.5 sm:p-2 rounded-full transition-all',
                            isListening 
                                ? 'bg-red-500 text-white animate-pulse' 
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                        ]"
                        :disabled="isLoading"
                        title="Hablar para escribir mensaje"
                    >
                        <svg v-if="!isListening" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3 3H5a3 3 0 01-3-3V5a3 3 0 013-3h14a3 3 0 013 3v6a3 3 0 01-3 3z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4 sm:w-5 sm:h-5 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"></path>
                            <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.87 3.13 7 7 7s7-3.13 7-7h-2z"></path>
                        </svg>
                    </button>
                    
                    <!-- Botón de enviar -->
                    <button
                        @click="sendMessage"
                        :disabled="isLoading || !message.trim()"
                        class="p-1.5 sm:p-2 bg-red-600 text-white rounded-full hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
