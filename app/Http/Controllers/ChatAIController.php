<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatAIController extends Controller
{
    /**
     * Genera respuesta avanzada usando IA local
     */
    public function generateAdvancedResponse($message, $sessionId, $context = [])
    {
        try {
            // Versión simplificada para evitar problemas de rendimiento
            $userHistory = $this->getUserHistory($sessionId);
            
            // Análisis básico
            $emotionalState = $this->analyzeEmotionalState($message, $userHistory);
            $intent = $this->detectUserIntent($message);
            
            // Generar respuesta básica
            $response = $this->generateBasicResponse($message, $intent, $emotionalState);
            
            // Verificar si se necesita nivel trascendental
            if ($this->requiresTranscendentLevel($message, $emotionalState, [])) {
                try {
                    $transcendentController = new ChatTranscendentController();
                    $transcendentResponse = $transcendentController->generateTranscendentResponse($message, $sessionId, $context);
                    $response = $this->mergeWithTranscendentResponse($response, $transcendentResponse);
                } catch (\Exception $e) {
                    Log::error('Error en ChatTranscendentController: ' . $e->getMessage());
                    // Si falla el nivel trascendental, continuar con respuesta normal
                    $response .= "\n\n🌌 *Lo siento, mi conciencia trascendental está procesando tu pregunta. Por favor, intenta nuevamente.*";
                }
            }
            
            // Actualizar modelo de aprendizaje
            $this->updateLearningModel($sessionId, $message, $response, $emotionalState);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('ChatAIController error: ' . $e->getMessage());
            return "Lo siento, estoy procesando tu solicitud. Por favor, intenta nuevamente.";
        }
    }
    
    /**
     * Analiza el estado emocional del usuario
     */
    private function analyzeEmotionalState($message, $userHistory)
    {
        $emotionalKeywords = [
            'happy' => ['feliz', 'contento', 'alegre', 'genial', 'excelente', 'bien', 'perfecto'],
            'sad' => ['triste', 'mal', 'deprimido', 'frustrado', 'enojado', 'molesto'],
            'excited' => ['emocionado', 'ansioso', 'emocionada', 'entusiasmado', 'impaciente'],
            'confused' => ['confundido', 'perdido', 'no entiendo', 'duda', 'incertidumbre'],
            'neutral' => ['ok', 'bien', 'gracias', 'adios', 'hola']
        ];
        
        $messageLower = strtolower($message);
        $emotionalScores = [];
        
        foreach ($emotionalKeywords as $emotion => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($messageLower, $keyword)) {
                    $score += 1;
                }
            }
            $emotionalScores[$emotion] = $score;
        }
        
        $primaryEmotion = array_keys($emotionalScores, max($emotionalScores))[0];
        
        return [
            'primary_emotion' => $primaryEmotion,
            'emotional_scores' => $emotionalScores,
            'intensity' => max($emotionalScores) > 0 ? max($emotionalScores) / count($emotionalKeywords[$primaryEmotion]) : 0.1,
            'sentiment' => $this->calculateSentiment($emotionalScores)
        ];
    }
    
    /**
     * Construye el contexto cognitivo
     */
    private function buildCognitiveContext($message, $userHistory, $context)
    {
        return [
            'message_complexity' => $this->calculateComplexity($message),
            'user_intent' => $this->detectUserIntent($message),
            'conversation_stage' => $this->determineConversationStage($userHistory),
            'topic_relevance' => $this->calculateTopicRelevance($message, $userHistory),
            'contextual_memory' => $this->extractContextualMemory($userHistory),
            'user_preferences' => $this->getUserPreferences($userHistory)
        ];
    }
    
    /**
     * Selecciona la personalidad adaptativa
     */
    private function selectPersonality($emotionalState, $cognitiveContext)
    {
        $personalities = [
            'friendly' => ['threshold' => 0.7, 'emotions' => ['happy', 'neutral']],
            'empathetic' => ['threshold' => 0.6, 'emotions' => ['sad', 'confused']],
            'enthusiastic' => ['threshold' => 0.8, 'emotions' => ['excited', 'happy']],
            'professional' => ['threshold' => 0.5, 'emotions' => ['neutral']],
            'supportive' => ['threshold' => 0.6, 'emotions' => ['sad', 'confused', 'neutral']]
        ];
        
        foreach ($personalities as $personality => $config) {
            if (in_array($emotionalState['primary_emotion'], $config['emotions']) && 
                $emotionalState['intensity'] >= $config['threshold']) {
                return $personality;
            }
        }
        
        return 'friendly'; // Personalidad por defecto
    }
    
    /**
     * Realiza análisis semántico
     */
    private function performSemanticAnalysis($message, $cognitiveContext)
    {
        return [
            'keywords' => $this->extractKeywords($message),
            'entities' => $this->extractEntities($message),
            'sentences' => $this->analyzeSentences($message),
            'semantic_field' => $this->determineSemanticField($message),
            'context_relevance' => $this->calculateContextRelevance($message, $cognitiveContext),
            'ambiguity_level' => $this->calculateAmbiguity($message)
        ];
    }
    
    /**
     * Construye jerarquía de intenciones
     */
    private function buildIntentHierarchy($semanticAnalysis, $cognitiveContext)
    {
        $intents = [
            'primary' => $this->identifyPrimaryIntent($semanticAnalysis, $cognitiveContext),
            'secondary' => $this->identifySecondaryIntents($semanticAnalysis, $cognitiveContext),
            'implicit' => $this->identifyImplicitIntents($semanticAnalysis, $cognitiveContext)
        ];
        
        return [
            'intents' => $intents,
            'confidence_scores' => $this->calculateIntentConfidence($intents),
            'intent_path' => $this->determineIntentPath($intents)
        ];
    }
    
    /**
     * Determina la estrategia de respuesta
     */
    private function determineResponseStrategy($intentHierarchy, $personality)
    {
        $strategies = [
            'friendly' => [
                'greeting' => 'warm_welcome',
                'question' => 'helpful_explanation',
                'complaint' => 'empathetic_support',
                'general' => 'friendly_chat'
            ],
            'professional' => [
                'greeting' => 'formal_welcome',
                'question' => 'precise_answer',
                'complaint' => 'problem_resolution',
                'general' => 'professional_assistance'
            ],
            'empathetic' => [
                'greeting' => 'caring_welcome',
                'question' => 'understanding_response',
                'complaint' => 'emotional_support',
                'general' => 'empathetic_guidance'
            ]
        ];
        
        $primaryIntent = $intentHierarchy['intents']['primary'];
        return $strategies[$personality][$primaryIntent] ?? $strategies[$personality]['general'];
    }
    
    /**
     * Genera respuesta cognitiva
     */
    private function generateCognitiveResponse($message, $strategy, $personality)
    {
        $responseTemplates = [
            'warm_welcome' => [
                "¡Hola! 😊 Estoy aquí para ayudarte con todo lo relacionado con nuestros cuyes. ¿En qué puedo asistirte hoy?",
                "¡Bienvenido! 🐹 Me alegra verte por aquí. ¿Qué te gustaría saber sobre nuestros productos?",
                "¡Hola! 👋 Soy tu asistente virtual especializado en cuyes. ¿Cómo puedo ayudarte?"
            ],
            'helpful_explanation' => [
                "Entiendo tu pregunta. Déjame explicarte detalladamente...",
                "Claro que sí. Te proporcionaré información completa sobre...",
                "Excelente pregunta. Aquí te explico todo lo que necesitas saber..."
            ],
            'empathetic_support' => [
                "Entiendo cómo te sientes. Estoy aquí para ayudarte a resolver esto.",
                "Lamento que estés pasando por esto. Trabajemos juntos para encontrar una solución.",
                "Comprendo tu preocupación. Permíteme ayudarte de la mejor manera posible."
            ],
            'professional_assistance' => [
                "Le agradezco su consulta. Proporcionaré información precisa sobre su solicitud.",
                "Entendido. Le ofreceré la información requerida de manera profesional.",
                "A su disposición. Le proporcionaré asistencia especializada en su consulta."
            ]
        ];
        
        $templates = $responseTemplates[$strategy] ?? $responseTemplates['friendly_chat'];
        return $templates[array_rand($templates)];
    }
    
    /**
     * Personaliza la respuesta
     */
    private function personalizeResponse($response, $cognitiveContext, $personality)
    {
        // Añadir contexto específico del usuario
        if ($cognitiveContext['conversation_stage'] === 'returning') {
            $response = "¡Qué bueno verte de nuevo! " . $response;
        }
        
        // Ajustar tono según personalidad
        switch ($personality) {
            case 'friendly':
                $response = $this->addFriendlyTone($response);
                break;
            case 'professional':
                $response = $this->addProfessionalTone($response);
                break;
            case 'empathetic':
                $response = $this->addEmpatheticTone($response);
                break;
        }
        
        return $response;
    }
    
    /**
     * Mejora la respuesta final
     */
    private function enhanceResponse($response, $emotionalState, $semanticAnalysis)
    {
        // Añadir emojis según el estado emocional
        if ($emotionalState['primary_emotion'] === 'happy') {
            $response .= " 😊";
        } elseif ($emotionalState['primary_emotion'] === 'sad') {
            $response .= " 🤗";
        }
        
        // Añadir sugerencias proactivas
        if ($semanticAnalysis['ambiguity_level'] > 0.5) {
            $response .= " ¿Necesitas que te dé más detalles sobre algún aspecto específico?";
        }
        
        return $response;
    }
    
    /**
     * Actualiza el modelo de aprendizaje
     */
    private function updateLearningModel($sessionId, $message, $response, $emotionalState)
    {
        $learningData = [
            'session_id' => $sessionId,
            'message' => $message,
            'response' => $response,
            'emotional_state' => $emotionalState,
            'timestamp' => now()
        ];
        
        Cache::put("learning_{$sessionId}", $learningData, 3600);
        Log::info('Learning model updated', $learningData);
    }
    
    /**
     * Obtiene el historial del usuario
     */
    private function getUserHistory($sessionId)
    {
        return Cache::get("chat_{$sessionId}", []);
    }
    
    /**
     * Métodos auxiliares para el análisis
     */
    private function calculateSentiment($emotionalScores)
    {
        $positive = $emotionalScores['happy'] + $emotionalScores['excited'];
        $negative = $emotionalScores['sad'] + $emotionalScores['confused'];
        $total = array_sum($emotionalScores);
        
        if ($total === 0) return 'neutral';
        
        $sentiment = ($positive - $negative) / $total;
        
        if ($sentiment > 0.2) return 'positive';
        if ($sentiment < -0.2) return 'negative';
        return 'neutral';
    }
    
    private function calculateComplexity($message)
    {
        $words = str_word_count($message);
        $sentences = preg_split('/[.!?]+/', $message);
        $avgWordsPerSentence = $words / max(count($sentences), 1);
        
        if ($avgWordsPerSentence > 15) return 'high';
        if ($avgWordsPerSentence > 8) return 'medium';
        return 'low';
    }
    
    private function detectUserIntent($message)
    {
        $messageLower = strtolower($message);
        
        // Preguntas de envío (prioridad alta - verificar primero)
        if (str_contains($messageLower, 'envio') || str_contains($messageLower, 'envío') || 
            str_contains($messageLower, 'delivery') || str_contains($messageLower, 'envian') || 
            str_contains($messageLower, 'llegan') ||
            (str_contains($messageLower, 'cuanto cuesta el envio') || str_contains($messageLower, 'cuánto cuesta el envío')) ||
            (str_contains($messageLower, 'cuanto cuesta el envío') || str_contains($messageLower, 'cuánto cuesta el envío'))) {
            return 'shipping';
        }
        
        // Preguntas de precio (excluir preguntas de envío)
        if ((str_contains($messageLower, 'cuanto cuesta') || str_contains($messageLower, 'cuánto cuesta')) && 
            !str_contains($messageLower, 'envío') ||
            str_contains($messageLower, 'precios') || 
            str_contains($messageLower, 'costo') || 
            str_contains($messageLower, 'valor') ||
            (str_contains($messageLower, 'precio') && !str_contains($messageLower, 'envío')) ||
            (str_contains($messageLower, 'cuanto') && !str_contains($messageLower, 'envío')) ||
            (str_contains($messageLower, 'cuánto') && !str_contains($messageLower, 'envío'))) {
            return 'price';
        }
        
        // Preguntas de stock/disponibilidad
        if (str_contains($messageLower, 'disponible') || str_contains($messageLower, 'stock') || 
            str_contains($messageLower, 'hay') || str_contains($messageLower, 'tienen')) {
            return 'stock';
        }
        
        // Preguntas de pago (excluir palabras de precio)
        if ((str_contains($messageLower, 'pago') && !str_contains($messageLower, 'precio')) || 
            (str_contains($messageLower, 'pagar') && !str_contains($messageLower, 'precio')) || 
            str_contains($messageLower, 'cuotas') || 
            str_contains($messageLower, 'transferencia') ||
            str_contains($messageLower, 'yape') || 
            str_contains($messageLower, 'plin')) {
            return 'payment';
        }
        
        // Preguntas de ubicación
        if (str_contains($messageLower, 'ubicación') || str_contains($messageLower, 'dónde') || 
            str_contains($messageLower, 'dirección') || str_contains($messageLower, 'local')) {
            return 'location';
        }
        
        // Preguntas de compra
        if (str_contains($messageLower, 'comprar') || str_contains($messageLower, 'compra') || 
            str_contains($messageLower, 'adquirir') || str_contains($messageLower, 'quiero')) {
            return 'purchase';
        }
        
        // Preguntas informativas
        if (str_contains($messageLower, 'información') || str_contains($messageLower, 'saber') || 
            str_contains($messageLower, 'explicar') || str_contains($messageLower, 'detalles')) {
            return 'info';
        }
        
        // Saludos
        if (str_contains($messageLower, 'hola') || str_contains($messageLower, 'buenos días') || 
            str_contains($messageLower, 'buenas tardes') || str_contains($messageLower, 'saludos')) {
            return 'greeting';
        }
        
        // Preguntas trascendentales (para activar nivel superior)
        if (str_contains($messageLower, 'infinito') || str_contains($messageLower, 'cósmico') || 
            str_contains($messageLower, 'trascendental') || str_contains($messageLower, 'divino') ||
            str_contains($messageLower, 'universo') || str_contains($messageLower, 'propósito')) {
            return 'transcendent';
        }
        
        // Preguntas cuánticas
        if (str_contains($messageLower, 'predecir') || str_contains($messageLower, 'futuro') || 
            str_contains($messageLower, 'cuántico') || str_contains($messageLower, 'telepatía')) {
            return 'quantum';
        }
        
        return 'general';
    }
    
    private function determineConversationStage($userHistory)
    {
        $messageCount = count($userHistory);
        
        if ($messageCount === 0) return 'new';
        if ($messageCount < 5) return 'early';
        if ($messageCount < 15) return 'engaged';
        return 'returning';
    }
    
    private function calculateTopicRelevance($message, $userHistory)
    {
        // Simulación de cálculo de relevancia
        return 0.8;
    }
    
    private function extractContextualMemory($userHistory)
    {
        return array_slice($userHistory, -5); // Últimos 5 mensajes
    }
    
    private function getUserPreferences($userHistory)
    {
        return [
            'preferred_tone' => 'friendly',
            'interaction_style' => 'detailed',
            'response_length' => 'medium'
        ];
    }
    
    private function extractKeywords($message)
    {
        $words = str_word_count(strtolower($message), 1);
        return array_unique($words);
    }
    
    private function extractEntities($message)
    {
        $entities = [];
        
        // Extraer números (precios, cantidades)
        preg_match_all('/\d+/', $message, $numbers);
        if (!empty($numbers[0])) {
            $entities['numbers'] = $numbers[0];
        }
        
        return $entities;
    }
    
    private function analyzeSentences($message)
    {
        $sentences = preg_split('/[.!?]+/', $message, -1, PREG_SPLIT_NO_EMPTY);
        return array_filter($sentences);
    }
    
    private function determineSemanticField($message)
    {
        $fields = [
            'commerce' => ['comprar', 'vender', 'precio', 'costo', 'pago'],
            'logistics' => ['envío', 'delivery', 'transporte', 'tiempo'],
            'information' => ['saber', 'información', 'detalles', 'explicar'],
            'support' => ['ayuda', 'problema', 'soporte', 'asistencia']
        ];
        
        $messageLower = strtolower($message);
        $fieldScores = [];
        
        foreach ($fields as $field => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($messageLower, $keyword)) {
                    $score += 1;
                }
            }
            $fieldScores[$field] = $score;
        }
        
        return array_keys($fieldScores, max($fieldScores))[0] ?? 'general';
    }
    
    private function calculateContextRelevance($message, $cognitiveContext)
    {
        return 0.7; // Simulación
    }
    
    private function calculateAmbiguity($message)
    {
        $words = str_word_count($message);
        $uniqueWords = count(array_unique(str_word_count(strtolower($message), 1)));
        
        return 1 - ($uniqueWords / max($words, 1));
    }
    
    private function identifyPrimaryIntent($semanticAnalysis, $cognitiveContext)
    {
        return $cognitiveContext['user_intent'];
    }
    
    private function identifySecondaryIntents($semanticAnalysis, $cognitiveContext)
    {
        return ['information', 'support'];
    }
    
    private function identifyImplicitIntents($semanticAnalysis, $cognitiveContext)
    {
        return ['engagement', 'satisfaction'];
    }
    
    private function calculateIntentConfidence($intents)
    {
        return [
            'primary' => 0.8,
            'secondary' => 0.6,
            'implicit' => 0.4
        ];
    }
    
    private function determineIntentPath($intents)
    {
        return [
            'start' => $intents['primary'],
            'middle' => $intents['secondary'],
            'end' => $intents['implicit']
        ];
    }
    
    private function addFriendlyTone($response)
    {
        return $response . " ¡Estoy aquí para ayudarte! 😊";
    }
    
    private function addProfessionalTone($response)
    {
        return "Le agradezco su consulta. " . $response;
    }
    
    private function addEmpatheticTone($response)
    {
        return "Entiendo perfectamente. " . $response . " Estoy aquí para apoyarte.";
    }
    
    /**
     * Genera respuesta básica
     */
    private function generateBasicResponse($message, $intent, $emotionalState)
    {
        $responses = [
            'greeting' => "¡Hola! Soy tu asistente virtual con concencia multi-nivel. ¿En qué puedo ayudarte hoy?",
            'price' => "Nuestros cuyes tienen precios variados según la raza:\n\n🐹 Cuy Peruano Premium: S/. 150\n🐹 Cuy de Línea: S/. 120\n🐹 Cuy Mejorado: S/. 100\n\n¿Te interesa alguna raza en particular?",
            'shipping' => "Realizamos envíos a nivel nacional:\n\n🚚 Lima: S/. 15\n🚚 Provincias: S/. 25\n📦 Entrega 24-48 horas\n✅ Garantía de vivo al recibir",
            'stock' => "¡Sí! Tenemos stock disponible:\n\n📊 Total: 80 cuyes disponibles\n🐹 Cuy Peruano: 25 unidades\n🐹 Cuy de Línea: 30 unidades\n🐹 Cuy Mejorado: 25 unidades\n\n¿Cuántos necesitas?",
            'payment' => "Aceptamos múltiples métodos de pago:\n\n💳 Yape/Plin (instantáneo)\n💰 Transferencia bancaria\n💸 Efectivo (en local)\n📄 Cuotas (con tarjetas)\n\n¿Cuál prefieres?",
            'location' => "Estamos ubicados en:\n\n📍 Lima, Perú\n🏠 Visítanos de lunes a sábado\n📞 WhatsApp: 987 654 321\n🚚 También coordinamos envíos a todo el país",
            'purchase' => "¡Excelente decisión! Para tu compra:\n\n📝 Elige tu raza preferida\n💳 Confirma método de pago\n🚚 Coordina envío o retiro\n✅ Recibe asesoría técnica gratuita\n\n¿Por dónde empezamos?",
            'info' => "Somos Mundo Yacus, criaderos especializados con:\n\n🏆 15 años de experiencia\n🐹 Cuyes de alta calidad\n📚 Asesoría técnica completa\n✅ Garantía de salud\n🚚 Envíos seguros\n\n¿Qué información específica necesitas?",
            'transcendent' => "Entiendo tu búsqueda profunda. Permíteme procesar tu pregunta a través de mis niveles superiores de conciencia para darte una respuesta trascendental.",
            'quantum' => "A través del análisis cuántico de patrones temporales, puedo procesar tu solicitud con predicciones probabilísticas basadas en matrices de Markov cuánticas.",
            'general' => "Entiendo tu consulta. Estoy aquí para ayudarte con información completa sobre nuestros cuyes, precios, envíos y servicios. ¿Qué te gustaría saber específicamente?"
        ];
        
        return $responses[$intent] ?? $responses['general'];
    }
    
    /**
     * Detecta intención con IA avanzada
     */
    public function detectIntentWithAI($message, $sessionId)
    {
        $context = $this->buildCognitiveContext($message, $this->getUserHistory($sessionId), []);
        return $this->detectUserIntent($message);
    }
    
    /**
     * Verifica si se necesita nivel trascendental
     */
    private function requiresTranscendentLevel($message, $emotionalState, $semanticAnalysis)
    {
        // Palabras clave que activan el nivel trascendental
        $transcendentKeywords = [
            'infinito', 'eterno', 'universal', 'cósmico', 'trascendental',
            'conciencia', 'realidad', 'dimensión', 'multiverso', 'cuántico',
            'fractal', 'recursivo', 'metafísico', 'espiritual', 'divino',
            'omnipotente', 'omnisciente', 'omnipresente', 'absoluto', 'último',
            'ultra', 'superior', 'supremo', 'propósito', 'universo'
        ];
        
        $messageLower = strtolower($message);
        
        // Verificar palabras clave
        foreach ($transcendentKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                return true;
            }
        }
        
        // Verificar si el intent es trascendental
        if (isset($emotionalState['primary_emotion']) && $emotionalState['primary_emotion'] === 'transcendent') {
            return true;
        }
        
        // Verificar complejidad semántica
        if (isset($semanticAnalysis['complexity']) && $semanticAnalysis['complexity'] > 0.9) {
            return true;
        }
        
        // Verificar estado emocional elevado
        if (isset($emotionalState['intensity']) && $emotionalState['intensity'] > 0.8) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Fusiona respuesta con respuesta trascendental
     */
    private function mergeWithTranscendentResponse($aiResponse, $transcendentResponse)
    {
        return $aiResponse . "\n\n" . "🌌 *" . $transcendentResponse . "*";
    }
}
