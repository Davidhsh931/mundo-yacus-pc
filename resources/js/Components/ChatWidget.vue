<script setup>
import { ref, nextTick, onMounted } from 'vue';

const isOpen = ref(false);
const message = ref('');
const messages = ref([
    { id: 1, type: 'bot', text: '¡Hola! Soy el asistente de Mundo Yacus. ¿En qué puedo ayudarte?' }
]);
const isLoading = ref(false);
const messagesContainer = ref(null);
const sessionId = ref(null);
const messageInput = ref(null);
const quickReplies = ref([]);

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

const sendMessage = async (quickReplyValue = null) => {
    // Forzar validación segura con conversión explícita a string
    const messageText = String(quickReplyValue || message.value || '').trim();
    
    if (!messageText || isLoading.value) return;

    // Add user message
    const userMessage = {
        id: Date.now(),
        type: 'user',
        text: messageText
    };
    messages.value.push(userMessage);
    await scrollToBottom();

    // Clear input and set loading
    if (!quickReplyValue) {
        message.value = '';
    }
    quickReplies.value = [];
    isLoading.value = true;

    try {
        const response = await fetch('/api/chat/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ 
                message: messageText,
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
            text: data.reply,
            timestamp: Date.now()
        });
        
        // Add quick replies if provided
        if (data.quick_replies && data.quick_replies.length > 0) {
            quickReplies.value = data.quick_replies;
        }
        
        await scrollToBottom();
        await focusInput();
    } catch (error) {
        console.error('Error sending message:', error);
        
        messages.value.push({
            id: Date.now() + 1,
            type: 'bot',
            text: 'Lo siento, hubo un error. Por favor intenta nuevamente.',
            timestamp: Date.now()
        });
        await scrollToBottom();
        await focusInput();
    } finally {
        isLoading.value = false;
        await focusInput();
    }
};

const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

const formatMessage = (text) => {
    // Convertir **texto** a <strong>texto</strong>
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong class="font-semibold">$1</strong>');
    
    // Convertir - texto a viñetas con formato
    text = text.replace(/^- (.+)$/gm, '<li class="ml-2 text-gray-700">• $1</li>');
    
    // Convertir múltiples <li> a <ul>
    text = text.replace(/(<li[^>]*>.*?<\/li>\s*)+/gs, match => {
        return '<ul class="list-none space-y-1">' + match + '</ul>';
    });
    
    return text;
};

// Efecto de escritura para mensajes del bot
const typingEffect = ref(false);
const displayedText = ref('');
const currentMessage = ref('');

</script>

<template>
    <div class="fixed bottom-4 right-4 z-50">
        <!-- Floating Button -->
        <button
            @click="toggleChat"
            class="w-12 h-12 sm:w-14 sm:h-14 bg-red-600 hover:bg-red-700 text-white rounded-full shadow-lg flex items-center justify-center z-40 border-2 border-white"
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
        <div 
            v-if="isOpen" 
            class="fixed bottom-20 right-4 w-full max-w-sm md:w-96 h-[500px] md:h-[600px] bg-white rounded-2xl shadow-2xl flex flex-col z-50 border border-gray-200 overflow-hidden transition-all duration-300 ease-out"
            :class="{ 'scale-95 opacity-0': !isOpen, 'scale-100 opacity-100': isOpen }"
        >
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4 rounded-t-2xl flex items-center justify-between shrink-0 shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md">
                        <span class="text-red-600 font-bold text-lg">🐹</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-base">Mundo Yacus</h3>
                        <div class="flex items-center gap-2">
                            <p class="text-xs opacity-90">Asistente Virtual</p>
                            <div class="flex items-center gap-1">
                                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                <span class="text-xs text-green-100 hidden sm:inline">En línea</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button
                    @click="toggleChat"
                    class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition-all duration-200 transform hover:scale-110"
                    title="Cerrar chat"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Messages Container -->
            <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gradient-to-b from-gray-50 to-white">
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    class="space-y-2 animate-fade-in"
                >
                    <div v-if="msg.type === 'user'" class="flex justify-end">
                        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-3 rounded-2xl rounded-tr-md shadow-lg max-w-[80%] sm:max-w-[85%] break-words transform transition-all duration-200 hover:shadow-xl">
                            <p class="text-sm font-medium">{{ msg.text }}</p>
                            <span class="text-xs opacity-75 block mt-1">{{ new Date(msg.timestamp || Date.now()).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                        </div>
                    </div>
                    <div v-else class="flex justify-start">
                        <div class="bg-white text-gray-800 p-3 rounded-2xl rounded-bl-md shadow-lg max-w-[80%] sm:max-w-[85%] break-words border border-gray-100 transform transition-all duration-200 hover:shadow-xl">
                            <div class="text-sm leading-relaxed whitespace-pre-line" v-html="formatMessage(msg.text)"></div>
                            <span class="text-xs text-gray-500 block mt-2">{{ new Date(msg.timestamp || Date.now()).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                            
                            <!-- Quick Replies para el último mensaje del bot -->
                            <div v-if="msg === messages[messages.length - 1] && msg.type === 'bot' && quickReplies.length > 0" class="mt-3 grid grid-cols-2 gap-2">
                                <button
                                    v-for="reply in quickReplies"
                                    :key="reply.text"
                                    @click="sendMessage(reply.value)"
                                    class="p-2 text-xs bg-gradient-to-r from-gray-50 to-gray-100 hover:from-gray-100 hover:to-gray-200 rounded-xl transition-all duration-200 text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 hover:shadow-md transform hover:scale-105"
                                    :disabled="isLoading"
                                >
                                    {{ reply.text }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="isLoading" class="bg-white text-gray-800 p-3 rounded-2xl rounded-bl-md shadow-lg max-w-[80%] sm:max-w-[85%] border border-gray-100">
                    <div class="flex gap-2 items-center">
                        <div class="w-2 h-2 bg-red-500 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-red-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-red-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <span class="text-xs text-gray-500 ml-2">Escribiendo...</span>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="p-4 bg-gradient-to-r from-white to-gray-50 border-t border-gray-200">
                <div class="flex items-center gap-2">
                    <input
                        ref="messageInput"
                        v-model="message"
                        @keypress="handleKeyPress"
                        type="text"
                        placeholder="Escribe tu mensaje..."
                        class="flex-1 px-4 py-3 text-sm border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 shadow-sm"
                        :disabled="isLoading"
                    />
                    
                    <button
                        @click="sendMessage()"
                        :disabled="isLoading || !message.trim()"
                        class="p-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-full hover:from-red-700 hover:to-red-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-110 shadow-lg hover:shadow-xl"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
