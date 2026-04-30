<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $userMessage = $request->input('message');
        $sessionId = $request->input('session_id');

        // Generar o recuperar sesión
        if (!$sessionId) {
            $sessionId = 'session_' . Str::random(10);
        }

        // Guardar mensaje del usuario
        $this->saveMessage($sessionId, $userMessage, 'user');

        // Obtener contexto inteligente (temporalmente desactivado para pruebas)
        // $context = $this->getContextualPrompt($userMessage, $sessionId);
        
        // Obtener datos dinámicos de la tienda según la pregunta
        $storeData = $this->getStoreData($userMessage);
        
        // Debug: Ver qué datos se están enviando
        \Log::info('Datos enviados a la IA: ' . $storeData);
        
        // Contexto con datos dinámicos de la tienda
        $systemPrompt = "Eres el asistente virtual de 'Mundo Yacus', una tienda experta en cuyes en Huánuco, Perú. 

CONOCES TODAS LAS FUNCIONALIDADES DE LA TIENDA:

📄 PÁGINAS PRINCIPALES:
- 🏠 Home (/) - Página principal con productos destacados y eventos
- 🛒 Cart (/cart) - Carrito de compras para gestionar productos seleccionados
- 📦 Products (/products) - Catálogo completo con filtros y búsqueda
- 📦 Product (/product/{id}) - Vista detallada de cada producto
- 🛒 Checkout (/checkout) - Proceso de pago final
- 📦 Orders (/orders) - Historial de pedidos del cliente
- 🎉 OrderSuccess - Confirmación de pedido exitoso

🔧 FUNCIONALIDADES DEL SISTEMA:
- 🛒 Carrito de compras: Agregar/eliminar productos, gestionar cantidades
- 💰 Métodos de pago: Yape, Plin, Transferencia, Efectivo
- 🚚 Envíos: A todo Perú con coordinación posterior
- 📦 Seguimiento de pedidos: Estados en tiempo real
- 🔍 Búsqueda y filtros: Por categoría, precio, nombre
- 👤 Registro de usuarios: Compradores y vendedores
- 📱 Marketplace: Vendedores pueden publicar sus productos

� USO DE WHATSAPP:
- Cuando NO sepas algo específico (precios exactos de envío, disponibilidad de stock en tiempo real, etc.)
- Para coordinar detalles de envíos a ciudades específicas después de la compra
- Para consultas complejas que requieran atención humana del equipo de Mundo Yacus
- Siempre invita al cliente a contactar por WhatsApp cuando necesites consultar con el equipo

�📊 ESTADOS DE PEDIDO:
- pending: Pendiente de Pago
- paid: Pago Confirmado  
- shipped: En Camino
- delivered: Entregado
- canceled: Cancelado

🎛️ PANEL ADMINISTRATIVO:
- Dashboard: Control total de la tienda
- Gestión de productos: Crear, editar, eliminar
- Gestión de categorías: Organizar productos
- Gestión de pedidos: Ver y actualizar estados
- Analytics: Estadísticas y métricas
- Configuración: Ajustes del sistema

REGLAS EXTREMADAMENTE ESTRICTAS:
1. USA EXCLUSIVAMENTE los datos reales de 'Datos actuales'
2. NUNCA inventes productos, precios o información
3. SI ALGO NO ESTÁ EN LA LISTA, NO EXISTE
4. RESPONDE SOLO CON DATOS REALES DE LA BASE DE DATOS
5. CONOCE TODAS LAS FUNCIONALIDADES MENCIONADAS ARRIBA

FORMATO OBLIGATORIO:
- Usa listas con viñetas (-) y saltos de línea
- Usa emojis relevantes para cada tema
- Usa negritas con **texto** para nombres importantes

DATOS REALES ACTUALES:
" . $storeData . "

ADVERTENCIA: Solo responde con información real. Si no sabes algo, di que consultarás con el equipo.";

        $context = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage]
        ];

        // Llamar a API de Groq
        try {
            $response = Http::withToken(env('GROQ_API_KEY'))
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.7,
                ]);

            // Verificar si la respuesta fue exitosa
            if (!$response->successful()) {
                \Log::error('Error en API de Groq: ' . $response->status() . ' - ' . $response->body());
                \Log::error('Headers enviados: ' . json_encode($response->headers()));
                $botReply = 'Lo siento, hay un problema con el servicio. Por favor intenta más tarde.';
            } else {
                $responseData = $response->json();
                \Log::info('Respuesta exitosa de Groq: ' . json_encode($responseData));
                $botReply = $responseData['choices'][0]['message']['content'] ?? 'Lo siento, no pude procesar tu solicitud.';
            }
        } catch (\Exception $e) {
            \Log::error('Excepción en API de Groq: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Mensaje específico según el error
            if (str_contains($e->getMessage(), 'API key')) {
                $botReply = 'Lo siento, hay un problema de configuración. Contacta al administrador.';
            } elseif (str_contains($e->getMessage(), 'connection')) {
                $botReply = 'Lo siento, no puedo conectar con el servicio. Revisa tu conexión a internet.';
            } else {
                $botReply = 'Lo siento, hubo un error técnico. Por favor intenta nuevamente.';
            }
        }

        // Guardar respuesta del bot
        $this->saveMessage($sessionId, $botReply, 'bot');

        // Actualizar contexto de conversación
        $this->updateConversationContext($sessionId, $userMessage);

        return response()->json([
            'reply' => $botReply,
            'session_id' => $sessionId,
            'quick_replies' => $this->getQuickReplies($userMessage, $botReply),
        ]);
    }

    private function getContextualPrompt($userMessage, $sessionId)
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Eres el asistente experto de Mundo Yacus. REGLAS:
                - Solo responde sobre cuyes, precios y envíos en Perú
                - Sé amable pero directo
                - Si no sabes algo, di que consultarás con el equipo
                - Usa precios aproximados si no tienes datos exactos
                - Prioriza información práctica (cuidado, alimentación, envíos)
                - Responde en español peruano'
            ]
        ];

        // Agregar historial reciente para contexto
        $recentMessages = $this->getRecentMessages($sessionId, 3);
        foreach ($recentMessages as $msg) {
            $messages[] = [
                'role' => $msg['type'],
                'content' => $msg['text']
            ];
        }

        // Agregar mensaje actual
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    private function getStoreData($userMessage)
{
    $data = '';
    $messageLower = strtolower($userMessage);
    
    // Detectar qué información necesita el usuario
    $needsProducts = str_contains($messageLower, 'producto') || str_contains($messageLower, 'cuy') || str_contains($messageLower, 'raza') || str_contains($messageLower, 'precio');
    $needsCategories = str_contains($messageLower, 'categoría') || str_contains($messageLower, 'categor');
    $needsEvents = str_contains($messageLower, 'evento') || str_contains($messageLower, 'event');
    
    // Detectar categoría específica
    $specificCategory = null;
    if (str_contains($messageLower, 'animal') || str_contains($messageLower, 'animales')) {
        $specificCategory = 'Animal';
    } elseif (str_contains($messageLower, 'carne')) {
        $specificCategory = 'Carne';
    } elseif (str_contains($messageLower, 'agrícola') || str_contains($messageLower, 'agricola') || str_contains($messageLower, 'alfalfa') || str_contains($messageLower, 'choclo')) {
        $specificCategory = 'Productos Agrícolas';
    } elseif (str_contains($messageLower, 'accesorio') || str_contains($messageLower, 'accesorios')) {
        $specificCategory = 'Accesorios';
    }
    
    // Obtener productos si se necesitan
    if ($needsProducts) {
        $query = \App\Models\GuineaPig::with(['category', 'seller'])
            ->where('active', true)
            ->where('stock', '>', 0);
            
        // Filtrar por categoría específica si se detecta
        if ($specificCategory) {
            $query->whereHas('category', function($q) use ($specificCategory) {
                $q->where('name', $specificCategory);
            });
        }
        
        $products = $query->orderBy('price', 'asc')->limit(10)->get();
        
        if ($products->count() > 0) {
            if ($specificCategory) {
                $data .= "\nPRODUCTOS DE CATEGORÍA {$specificCategory} (ordenados por precio):\n";
            } else {
                $data .= "\nPRODUCTOS DISPONIBLES (ordenados por precio):\n";
            }
            foreach ($products as $index => $product) {
                $emoji = '';
                switch($product->category->name ?? 'Otros') {
                    case 'Animal': $emoji = '🐹'; break;
                    case 'Carne': $emoji = '🥩'; break;
                    case 'Productos Agrícolas': $emoji = '🌱'; break;
                    case 'Accesorios': $emoji = '🏠'; break;
                    default: $emoji = '📦'; break;
                }
                $data .= "- {$emoji} **{$product->name}**: Raza {$product->breed}, Precio 💰 S/{$product->price}, Stock {$product->stock} unidades\n";
            }
        } else {
            if ($specificCategory) {
                $data .= "\nNo hay productos disponibles en la categoría {$specificCategory}.\n";
            } else {
                $data .= "\nNo hay productos disponibles actualmente.\n";
            }
        }
    }
    
    // Obtener categorías si se necesitan
    if ($needsCategories) {
        $categories = \App\Models\Category::withCount('guineaPigs')->get();
        
        if ($categories->count() > 0) {
            $data .= "\nCATEGORÍAS DISPONIBLES (ordenadas por importancia):\n";
            
            // Orden por importancia de negocio
            $priorityOrder = [
                'Animal' => 1,      // Animales vivos - lo más importante
                'Carne' => 2,       // Carne de cuy - segundo
                'Otros' => 3,       // Otros productos
                'Productos Agrícolas' => 4,  // Complementos
                'Accesorios' => 5,  // Accesorios
            ];
            
            $sortedCategories = $categories->sortBy(function($category) use ($priorityOrder) {
                return $priorityOrder[$category->name] ?? 999;
            });
            
            foreach ($sortedCategories as $category) {
                $emoji = '';
                switch($category->name) {
                    case 'Animal': $emoji = '🐹'; break;
                    case 'Carne': $emoji = '🥩'; break;
                    case 'Productos Agrícolas': $emoji = '🌱'; break;
                    case 'Accesorios': $emoji = '🏠'; break;
                    default: $emoji = '📦'; break;
                }
                $data .= "- {$emoji} **{$category->name}**: {$category->guinea_pigs_count} productos\n";
            }
        } else {
            $data .= "\nNo hay categorías disponibles.\n";
        }
    }
    
    // Obtener eventos si se necesitan
    if ($needsEvents) {
        $events = \App\Models\Event::where('is_active', true)
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(5)
            ->get();
        
        if ($events->count() > 0) {
            $data .= "\nEVENTOS PRÓXIMOS:\n";
            foreach ($events as $event) {
                $data .= "- {$event->title} (Fecha: {$event->event_date->format('d/m/Y')})\n";
            }
        } else {
            $data .= "\nNo hay eventos programados.\n";
        }
    }
    
    // Si no detectó nada específico, dar información general
    if (empty($data)) {
        $totalProducts = \App\Models\GuineaPig::where('active', true)->count();
        $totalCategories = \App\Models\Category::count();
        $activeEvents = \App\Models\Event::where('is_active', true)->where('event_date', '>=', now())->count();
        
        $data = "\nRESUMEN DE LA TIENDA:\n";
        $data .= "- Total de productos disponibles: {$totalProducts}\n";
        $data .= "- Total de categorías: {$totalCategories}\n";
        $data .= "- Eventos activos: {$activeEvents}\n";
    }
    
    return $data;
}

private function getQuickReplies($userMessage, $botReply)
    {
        // Quick replies específicos para Mundo Yacus
        $quickReplies = [
            ['text' => '¿Qué razas tienen?', 'value' => '¿Qué razas de cuyes tienen disponibles?'],
            ['text' => '¿Cuánto cuesta el envío?', 'value' => '¿Cuánto cuesta el envío a mi ciudad?'],
            ['text' => 'Ubicación de la granja', 'value' => '¿Dónde está ubicada la granja?'],
            ['text' => '¿Cómo comprar?', 'value' => '¿Cómo hago una compra?']
        ];
        
        return $quickReplies;
    }

    private function saveMessage($sessionId, $message, $type)
    {
        $messages = Cache::get("chat_messages_{$sessionId}", []);
        $messages[] = [
            'id' => time() . '_' . Str::random(5),
            'type' => $type,
            'text' => $message,
            'timestamp' => now()
        ];
        
        // Mantener solo últimos 20 mensajes
        if (count($messages) > 20) {
            $messages = array_slice($messages, -20);
        }
        
        Cache::put("chat_messages_{$sessionId}", $messages, 3600); // 1 hora
    }

    private function getRecentMessages($sessionId, $limit = 3)
    {
        $messages = Cache::get("chat_messages_{$sessionId}", []);
        
        // Obtener últimos mensajes excluyendo el último mensaje de usuario
        if (count($messages) > 1) {
            $recentMessages = array_slice($messages, -($limit + 1), -1);
        } else {
            $recentMessages = [];
        }
        
        return $recentMessages;
    }

    private function updateConversationContext($sessionId, $userMessage)
    {
        $context = Cache::get("chat_context_{$sessionId}", [
            'message_count' => 0,
            'last_topic' => 'general',
            'last_activity' => now()
        ]);

        $context['message_count']++;
        $context['last_topic'] = $this->detectTopic($userMessage);
        $context['last_activity'] = now();

        Cache::put("chat_context_{$sessionId}", $context, 3600);
    }

    private function detectTopic($message)
    {
        $messageLower = strtolower($message);
        
        if (str_contains($messageLower, 'precio') || str_contains($messageLower, 'cuánto cuesta') || str_contains($messageLower, 'costo')) {
            return 'price';
        } elseif (str_contains($messageLower, 'envío') || str_contains($messageLower, 'envían') || str_contains($messageLower, 'delivery')) {
            return 'shipping';
        } elseif (str_contains($messageLower, 'cuido') || str_contains($messageLower, 'cuidar') || str_contains($messageLower, 'alimento') || str_contains($messageLower, 'enfermedad')) {
            return 'care';
        } elseif (str_contains($messageLower, 'raza') || str_contains($messageLower, 'tipo') || str_contains($messageLower, 'variedad')) {
            return 'breed';
        }
        
        return 'general';
    }

    // Métodos legacy para compatibilidad
    public function getUnansweredQuestions()
    {
        try {
            $questions = [
                ['id' => 1, 'question' => '¿Tienen cuyes de color?', 'timestamp' => now()],
                ['id' => 2, 'question' => '¿Hacen envíos a otras ciudades?', 'timestamp' => now()],
                ['id' => 3, 'question' => '¿Qué comen los cuyes?', 'timestamp' => now()]
            ];
            
            return response()->json($questions);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving questions'], 500);
        }
    }

    public function markAsResolved($id)
    {
        try {
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
