<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * ChatController - Controller principal de chat
 * 
 * NOTA: Este controller ha sido refactorizado.
 * La funcionalidad principal ahora está distribuida en:
 * - ChatBasicController: Funcionalidad básica del chat
 * - ChatAIController: Sistemas de IA avanzada
 * - ChatQuantumController: Funcionalidad cuántica
 * - ChatDivineController: Funcionalidad meta-cósmica
 * - ChatAnalyticsController: Métricas y estadísticas
 * 
 * Este archivo se mantiene por compatibilidad y redirección.
 */
class ChatController extends Controller
{
    /**
     * Endpoint principal de chat - Redirige al ChatBasicController
     */
    public function sendMessage(Request $request)
    {
        try {
            // Redirigir al ChatBasicController para mantener compatibilidad
            $basicController = new ChatBasicController();
            return $basicController->sendMessage($request);
            
        } catch (\Exception $e) {
            Log::error('ChatController error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error processing message',
                'response' => 'Lo siento, hubo un error al procesar tu mensaje. Por favor intenta nuevamente.',
                'type' => 'error'
            ], 500);
        }
    }
    
    /**
     * Tracking de botones - Redirige al ChatBasicController
     */
    public function trackButtonClick(Request $request)
    {
        try {
            $basicController = new ChatBasicController();
            return $basicController->trackButtonClick($request);
            
        } catch (\Exception $e) {
            Log::error('ChatController trackButtonClick error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
    
    /**
     * Rating de satisfacción - Método legacy para compatibilidad
     */
    public function rateSatisfaction(Request $request)
    {
        try {
            $sessionId = $request->input('session_id');
            $rating = $request->input('rating');
            
            Log::info("Satisfaction rated: $rating for session $sessionId");
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('ChatController rateSatisfaction error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
    
    /**
     * Obtener preguntas no respondidas - Método legacy
     */
    public function getUnansweredQuestions()
    {
        try {
            // Simulación de preguntas no respondidas
            $questions = [
                ['id' => 1, 'question' => '¿Tienen cuyes de color?', 'timestamp' => Carbon::now()],
                ['id' => 2, 'question' => '¿Hacen envíos a otras ciudades?', 'timestamp' => Carbon::now()],
                ['id' => 3, 'question' => '¿Qué comen los cuyes?', 'timestamp' => Carbon::now()]
            ];
            
            return response()->json($questions);
            
        } catch (\Exception $e) {
            Log::error('ChatController getUnansweredQuestions error: ' . $e->getMessage());
            return response()->json(['error' => 'Error retrieving questions'], 500);
        }
    }
    
    /**
     * Marcar pregunta como resuelta - Método legacy
     */
    public function markAsResolved($id)
    {
        try {
            Log::info("Question marked as resolved: $id");
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('ChatController markAsResolved error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
