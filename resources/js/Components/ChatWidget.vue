<script setup>
import { ref, nextTick } from 'vue';

const isOpen = ref(false);
const message = ref('');
const messages = ref([
    { id: 1, type: 'bot', text: '¡Hola! Soy el asistente de Mundo Yacus. ¿En qué puedo ayudarte?' }
]);
const isLoading = ref(false);
const messagesContainer = ref(null);
const sessionId = ref(null);
const messageInput = ref(null);

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
            text: data.response
        });
        await scrollToBottom();
        await focusInput();
    } catch (error) {
        console.error('Error sending message:', error);
        
        messages.value.push({
            id: Date.now() + 1,
            type: 'bot',
            text: 'Lo siento, hubo un error. Por favor intenta nuevamente.'
        });
        await scrollToBottom();
        await focusInput();
    }
    isLoading.value = false;
};

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
        <div v-if="isOpen" class="fixed bottom-20 right-4 w-full max-w-sm md:w-96 h-[500px] md:h-[600px] bg-white rounded-lg shadow-2xl flex flex-col z-50 border border-gray-200">
            <!-- Header -->
            <div class="bg-red-600 text-white p-3 sm:p-4 rounded-t-lg flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white rounded-full flex items-center justify-center">
                        <span class="text-red-600 font-bold text-sm sm:text-base"></span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm sm:text-base">Chat Mundo Yacus</h3>
                        <p class="text-xs sm:text-sm opacity-90">Asistente</p>
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
                            <span class="text-xs opacity-75 block mt-1">{{ new Date(msg.timestamp || Date.now()).toLocaleTimeString() }}</span>
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
                <div class="flex items-center gap-1 sm:gap-2">
                    <input
                        ref="messageInput"
                        v-model="message"
                        @keypress="handleKeyPress"
                        type="text"
                        placeholder="Escribe tu mensaje..."
                        class="flex-1 px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500"
                        :disabled="isLoading"
                    />
                    
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
