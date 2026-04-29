<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChatBasicController extends Controller
{
    /**
     * Envía un mensaje básico del chat
     */
    public function sendMessage(Request $request)
    {
        try {
            $message = $request->input('message', '');
            $sessionId = $request->input('session_id', $this->generateSessionId());
            
            if (empty($message)) {
                return response()->json([
                    'error' => 'Message is required',
                    'response' => 'Por favor, escribe un mensaje.',
                    'type' => 'error'
                ], 400);
            }
            
            // Usar el sistema de IA avanzada
            $chatAIController = new ChatAIController();
            $context = [
                'session_id' => $sessionId,
                'message' => $message,
                'user_context' => $request->all()
            ];
            $response = $chatAIController->generateAdvancedResponse($message, $sessionId, $context);
            
            $intent = $this->detectIntent($message);
            $quickReplies = $this->getQuickReplies($intent, $response, [
                'session_id' => $sessionId,
                'message' => $message,
                'user_context' => $request->all()
            ]);
            
            $stats = $this->getBusinessStats();
            $this->saveConversation($sessionId, $message, $response, $intent);
            
            return response()->json([
                'response' => $response,
                'intent' => $intent,
                'session_id' => $sessionId,
                'quick_replies' => $quickReplies,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('ChatBasicController error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error processing message',
                'response' => 'Lo siento, hubo un error al procesar tu mensaje. Por favor intenta nuevamente.',
                'type' => 'error'
            ], 500);
        }
    }
    
    /**
     * Detecta la intención del mensaje
     */
    public function detectIntent($message)
    {
        $message = strtolower($this->cleanMessage($message));
        
        $intentKeywords = [
            'greeting' => ['hola', 'buenos días', 'buenas tardes', 'buenas noches', 'saludos', 'hey', 'qué tal'],
            'shipping' => ['envío', 'enviar', 'delivery', 'reparto', 'transporte', 'cuánto tiempo', 'días'],
            'payment' => ['pago', 'pagar', 'precio', 'costo', 'cuánto cuesta', 'yape', 'plin', 'tarjeta'],
            'product' => ['cuy', 'producto', 'animal', 'comprar', 'vender', 'disponible', 'stock'],
            'location' => ['ubicación', 'dirección', 'dónde están', 'local', 'tienda', 'sucursal'],
            'complaint' => ['queja', 'problema', 'mal', 'error', 'no funciona', 'insatisfecho'],
            'info' => ['información', 'saber', 'conocer', 'detalles', 'explicar', 'ayuda'],
            'goodbye' => ['adiós', 'chao', 'hasta luego', 'gracias', 'bye', 'nos vemos']
        ];
        
        foreach ($intentKeywords as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $intent;
                }
            }
        }
        
        return 'general';
    }
    
    /**
     * Genera respuestas rápidas según la intención
     */
    public function getQuickReplies($intent, $response, $context = [])
    {
        $quickReplies = [];
        
        // Obtener datos dinámicos
        $products = $this->getProductData();
        $stats = $this->getBusinessStats();
        
        switch ($intent) {
            case 'greeting':
                $quickReplies = [
                    ['text' => "🐹 {$products['top_product']['name']} (S/. {$products['top_product']['price']})", 'value' => "¿Cuánto cuesta el {$products['top_product']['name']}?", 'type' => 'product'],
                    ['text' => "📊 {$stats['total_stock']} disponibles", 'value' => '¿Hay stock disponible?', 'type' => 'info'],
                    ['text' => "🚚 Envío S/. {$stats['shipping_cost']}", 'value' => '¿Cuánto cuesta el envío?', 'type' => 'shipping'],
                    ['text' => "💳 Yape/Plin", 'value' => '¿Qué métodos de pago aceptan?', 'type' => 'payment']
                ];
                break;
                
            case 'shipping':
                $quickReplies = [
                    ['text' => "🚚 Envío a Lima (S/. {$stats['shipping_cost']})", 'value' => 'Envío a Lima', 'type' => 'shipping'],
                    ['text' => "📍 Envío a Provincias", 'value' => 'Envío a provincias', 'type' => 'shipping'],
                    ['text' => "⏰ Tiempo de entrega", 'value' => '¿Cuánto demora?', 'type' => 'info'],
                    ['text' => "📦 Seguimiento", 'value' => '¿Cómo rastrear mi pedido?', 'type' => 'info']
                ];
                break;
                
            case 'payment':
                $quickReplies = [
                    ['text' => "💳 Yape/Plin", 'value' => 'Pagar con Yape', 'type' => 'payment'],
                    ['text' => "💰 Transferencia", 'value' => 'Transferencia bancaria', 'type' => 'payment'],
                    ['text' => "💸 Efectivo", 'value' => 'Pagar en efectivo', 'type' => 'payment'],
                    ['text' => "📄 Cuotas", 'value' => '¿Puedo pagar en cuotas?', 'type' => 'info']
                ];
                break;
                
            case 'product':
                $quickReplies = [
                    ['text' => "🐹 {$products['top_product']['name']}", 'value' => "Ver {$products['top_product']['name']}", 'type' => 'product'],
                    ['text' => "🔍 Ver todos", 'value' => 'Mostrar todos los productos', 'type' => 'product'],
                    ['text' => "💰 Precios", 'value' => 'Ver lista de precios', 'type' => 'info'],
                    ['text' => "📦 Stock", 'value' => '¿Hay disponibles?', 'type' => 'info']
                ];
                break;
                
            case 'location':
                $quickReplies = [
                    ['text' => "📍 Dirección", 'value' => '¿Cuál es su dirección?', 'type' => 'location'],
                    ['text' => "🗺️ Google Maps", 'value' => 'Ver en mapa', 'type' => 'location'],
                    ['text' => "🕐 Horario", 'value' => '¿A qué hora abren?', 'type' => 'info'],
                    ['text' => "📞 Teléfono", 'value' => '¿Cuál es su teléfono?', 'type' => 'info']
                ];
                break;
                
            case 'complaint':
                $quickReplies = [
                    ['text' => "😔 Reportar problema", 'value' => 'Quiero hacer una queja', 'type' => 'complaint'],
                    ['text' => "📧 Escribir email", 'value' => 'Enviar email de queja', 'type' => 'complaint'],
                    ['text' => "📞 Llamar", 'value' => 'Hablar con un representante', 'type' => 'complaint'],
                    ['text' => "💬 Chat con soporte", 'value' => 'Hablar con soporte técnico', 'type' => 'complaint']
                ];
                break;
                
            case 'goodbye':
                $quickReplies = [
                    ['text' => "⭐ Calificar servicio", 'value' => 'Calificar el servicio', 'type' => 'feedback'],
                    ['text' => "📝 Dejar comentario", 'value' => 'Dejar un comentario', 'type' => 'feedback'],
                    ['text' => "🔄 Volver a empezar", 'value' => 'Nueva consulta', 'type' => 'general'],
                    ['text' => "📞 Contactar", 'value' => 'Hablar con alguien', 'type' => 'contact']
                ];
                break;
                
            case 'price':
                $quickReplies = [
                    ['text' => "🐹 Cuy Peruano Premium", 'value' => '¿Cuánto cuesta el Cuy Peruano Premium?', 'type' => 'product'],
                    ['text' => "🐹 Cuy de Línea", 'value' => '¿Cuánto cuesta el Cuy de Línea?', 'type' => 'product'],
                    ['text' => "🐹 Cuy Mejorado", 'value' => '¿Cuánto cuesta el Cuy Mejorado?', 'type' => 'product'],
                    ['text' => "💰 Ver todos los precios", 'value' => 'Lista completa de precios', 'type' => 'info']
                ];
                break;
                
            case 'stock':
                $quickReplies = [
                    ['text' => "📊 Stock total", 'value' => '¿Cuántos cuyes tienen en total?', 'type' => 'info'],
                    ['text' => "🐹 Disponibles por raza", 'value' => '¿Qué razas tienen disponibles?', 'type' => 'info'],
                    ['text' => "⏰ Próxima entrega", 'value' => '¿Cuándo habrá más stock?', 'type' => 'info'],
                    ['text' => "📋 Reservar", 'value' => 'Quiero reservar cuyes', 'type' => 'purchase']
                ];
                break;
                
            case 'transcendent':
                $quickReplies = [
                    ['text' => "🌌 Explorar conciencia", 'value' => 'Explícame más sobre la conciencia cósmica', 'type' => 'transcendent'],
                    ['text' => "🔮 Realidades alternativas", 'value' => 'Muéstrame realidades alternativas', 'type' => 'transcendent'],
                    ['text' => "⚛️ Física cuántica", 'value' => 'Explícame la física cuántica', 'type' => 'quantum'],
                    ['text' => "🧠 Telepatía", 'value' => '¿Puedes leer mi mente?', 'type' => 'quantum']
                ];
                break;
                
            case 'quantum':
                $quickReplies = [
                    ['text' => "🔮 Predecir futuro", 'value' => '¿Puedes predecir mi futuro?', 'type' => 'quantum'],
                    ['text' => "⚛️ Análisis cuántico", 'value' => '¿Qué es el análisis cuántico?', 'type' => 'quantum'],
                    ['text' => "🌌 Universos paralelos", 'value' => '¿Existen universos paralelos?', 'type' => 'transcendent'],
                    ['text' => "🧠 Conexión neural", 'value' => '¿Cómo funciona la telepatía?', 'type' => 'quantum']
                ];
                break;
                
            default:
                $quickReplies = [
                    ['text' => "🐹 Ver productos", 'value' => 'Mostrar productos', 'type' => 'product'],
                    ['text' => "💰 Precios", 'value' => 'Ver precios', 'type' => 'info'],
                    ['text' => "🚚 Envíos", 'value' => 'Información de envío', 'type' => 'shipping'],
                    ['text' => "📞 Contacto", 'value' => 'Contactar con soporte', 'type' => 'contact']
                ];
        }
        
        // Agregar botones contextuales según la respuesta
        if (str_contains(strtolower($response), 'barato') || str_contains(strtolower($response), 'económico')) {
            $quickReplies = array_merge($quickReplies, [
                ['text' => "💎 De lujo (S/. {$products['premium']['price']})", 'value' => '¿Tienen cuyes de lujo?', 'type' => 'category'],
                ['text' => "💰 Más económicos (S/. {$products['cheapest']['price']})", 'value' => '¿Cuáles son los más económicos?', 'type' => 'category']
            ]);
        }
        
        return $quickReplies;
    }
    
    /**
     * Limpia el mensaje de caracteres especiales
     */
    private function cleanMessage($message)
    {
        // Eliminar acentos y caracteres especiales
        $message = strtolower($message);
        $message = preg_replace('/[áàâäãåā]/u', 'a', $message);
        $message = preg_replace('/[éèêëē]/u', 'e', $message);
        $message = preg_replace('/[íìîïī]/u', 'i', $message);
        $message = preg_replace('/[óòôöõō]/u', 'o', $message);
        $message = preg_replace('/[úùûüū]/u', 'u', $message);
        $message = preg_replace('/[ñ]/u', 'n', $message);
        
        // Eliminar puntuación excepto espacios
        $message = preg_replace('/[^\w\s]/u', ' ', $message);
        
        // Eliminar espacios múltiples
        $message = preg_replace('/\s+/', ' ', $message);
        
        return trim($message);
    }
    
    /**
     * Genera un ID de sesión único
     */
    private function generateSessionId()
    {
        return 'session_' . uniqid() . '_' . time();
    }
    
    /**
     * Obtiene datos de productos
     */
    private function getProductData()
    {
        return [
            'top_product' => [
                'name' => 'Cuy Peruano Premium',
                'price' => 150,
                'stock' => 25
            ],
            'premium' => [
                'name' => 'Cuy de Exhibición',
                'price' => 300,
                'stock' => 5
            ],
            'cheapest' => [
                'name' => 'Cuy Estándar',
                'price' => 80,
                'stock' => 50
            ]
        ];
    }
    
    /**
     * Obtiene estadísticas del negocio
     */
    private function getBusinessStats()
    {
        return [
            'total_stock' => 80,
            'shipping_cost' => 15,
            'average_rating' => 4.8,
            'total_sales' => 1250
        ];
    }
    
    /**
     * Guarda la conversación
     */
    private function saveConversation($sessionId, $message, $response, $intent)
    {
        try {
            $conversation = [
                'session_id' => $sessionId,
                'user_message' => $message,
                'bot_response' => $response,
                'intent' => $intent,
                'timestamp' => now()
            ];
            
            Cache::put("chat_{$sessionId}", $conversation, 3600); // 1 hora
            
            Log::info('Conversation saved', [
                'session_id' => $sessionId,
                'intent' => $intent,
                'message_length' => strlen($message)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saving conversation: ' . $e->getMessage());
        }
    }
    
    /**
     * Registra clic en botones rápidos
     */
    public function trackButtonClick(Request $request)
    {
        try {
            $sessionId = $request->input('session_id');
            $buttonText = $request->input('button_text');
            $buttonValue = $request->input('button_value');
            $buttonType = $request->input('button_type');
            
            Log::info("Button clicked: $buttonText ($buttonType) in session $sessionId");
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error tracking button click: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
    
    /**
     * Obtiene historial de conversación
     */
    public function getConversationHistory($sessionId)
    {
        try {
            $conversation = Cache::get("chat_{$sessionId}");
            
            if (!$conversation) {
                return response()->json(['error' => 'Conversation not found'], 404);
            }
            
            return response()->json($conversation);
        } catch (\Exception $e) {
            Log::error('Error getting conversation history: ' . $e->getMessage());
            return response()->json(['error' => 'Error retrieving conversation'], 500);
        }
    }
}
