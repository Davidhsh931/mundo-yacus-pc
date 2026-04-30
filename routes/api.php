<?php

use Illuminate\Http\Request;
use App\Http\Controllers\GuineaPigController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatAIController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Rutas API sin autenticación para llamadas desde JavaScript
Route::get('/cuy/sugerir-stock/{id}', [GuineaPigController::class, 'sugerirStock'])->name('api.cuy.sugerir-stock');

// Rutas de chat - Simplificadas
Route::prefix('chat')->group(function () {
    // Endpoint principal de chat con API de Groq
    Route::post('/message', [ChatController::class, 'sendMessage'])->name('api.chat.message');
    
    // IA avanzada (mantener por si se necesita)
    Route::post('/ai/response', [ChatAIController::class, 'generateAdvancedResponse'])->name('api.chat.ai.response');
    Route::post('/ai/intent', [ChatAIController::class, 'detectIntentWithAI'])->name('api.chat.ai.intent');
});

// Rutas legacy de ChatController (mantener compatibilidad)
Route::get('/chat/unanswered', [ChatController::class, 'getUnansweredQuestions'])->name('api.chat.unanswered');
Route::post('/chat/resolve/{id}', [ChatController::class, 'markAsResolved'])->name('api.chat.resolve');
