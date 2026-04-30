<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $userMessage = $request->input('message');

        // Usamos el endpoint de Groq y el modelo gratuito que viste en tus límites
        $response = Http::withToken(env('GROQ_API_KEY'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.1-8b-instant', // El modelo de tus 500k tokens diarios
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => 'Eres el asistente experto de Mundo Yacus. Responde de forma amable sobre cuyes, precios y envíos en Perú.'
                    ],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.7,
            ]);

        return response()->json([
            'reply' => $response->json()['choices'][0]['message']['content'] ?? 'Lo siento, hubo un error.',
        ]);
    }
}
