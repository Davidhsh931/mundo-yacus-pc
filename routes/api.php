<?php

use Illuminate\Http\Request;
use App\Http\Controllers\GuineaPigController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatBasicController;
use App\Http\Controllers\ChatAIController;
use App\Http\Controllers\ChatQuantumController;
use App\Http\Controllers\ChatDivineController;
use App\Http\Controllers\ChatTranscendentController;
use App\Http\Controllers\ChatAnalyticsController;
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

// Rutas de chat básicas
Route::prefix('chat')->group(function () {
    Route::post('/message', [ChatController::class, 'sendMessage'])->name('api.chat.message');
    Route::post('/track-button', [ChatBasicController::class, 'trackButtonClick'])->name('api.chat.track-button');
    Route::get('/history/{sessionId}', [ChatBasicController::class, 'getConversationHistory'])->name('api.chat.history');
    
    // IA avanzada
    Route::post('/ai/response', [ChatAIController::class, 'generateAdvancedResponse'])->name('api.chat.ai.response');
    Route::post('/ai/intent', [ChatAIController::class, 'detectIntentWithAI'])->name('api.chat.ai.intent');
    
    // Funcionalidad cuántica
    Route::post('/quantum/response', [ChatQuantumController::class, 'generateQuantumResponse'])->name('api.chat.quantum.response');
    
    // Funcionalidad meta-cósmica
    Route::post('/divine/response', [ChatDivineController::class, 'generateDivineResponse'])->name('api.chat.divine.response');
    
    // Funcionalidad ultra-trascendental
    Route::post('/transcendent/response', [ChatTranscendentController::class, 'generateTranscendentResponse'])->name('api.chat.transcendent.response');
    
    // Analytics
    Route::get('/analytics', [ChatAnalyticsController::class, 'getAnalytics'])->name('api.chat.analytics');
    Route::get('/analytics/realtime', [ChatAnalyticsController::class, 'getRealTimeActivity'])->name('api.chat.analytics.realtime');
    Route::get('/analytics/advanced', [ChatAnalyticsController::class, 'getAdvancedMetrics'])->name('api.chat.analytics.advanced');
    
    // Dashboard
    Route::get('/dashboard', [ChatAnalyticsController::class, 'index'])->name('api.chat.dashboard');
});

Route::get('/chat/unanswered', [ChatController::class, 'getUnansweredQuestions'])->name('api.chat.unanswered');
Route::post('/chat/resolve/{id}', [ChatController::class, 'markAsResolved'])->name('api.chat.resolve');
