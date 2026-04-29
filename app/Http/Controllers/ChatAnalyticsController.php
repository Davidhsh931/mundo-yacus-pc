<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Models\ChatAnalytics;
use App\Models\GuineaPig;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ChatAnalyticsController extends Controller
{
    public function index()
    {
        return view('chat.dashboard');
    }

    public function getAnalytics(Request $request)
    {
        $range = $request->get('range', 'today');
        
        switch ($range) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::tomorrow();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            default:
                $startDate = Carbon::today();
                $endDate = Carbon::tomorrow();
        }

        $stats = $this->getStats($startDate, $endDate);
        $charts = $this->getChartData($startDate, $endDate);
        $intents = $this->getTopIntents($startDate, $endDate);
        $products = $this->getPopularProducts($startDate, $endDate);

        return response()->json([
            'stats' => $stats,
            'charts' => $charts,
            'intents' => $intents,
            'products' => $products
        ]);
    }

    public function getRealTimeActivity()
    {
        $activities = [];
        
        // Simular actividad en tiempo real (en producción, esto vendría de eventos reales)
        $activities[] = [
            'id' => time(),
            'time' => Carbon::now()->format('H:i'),
            'user' => $this->getRandomUserName(),
            'action' => $this->getRandomAction(),
            'type' => $this->getRandomType()
        ];

        return response()->json($activities);
    }

    private function getStats($startDate, $endDate)
    {
        $totalMessages = ChatSession::whereBetween('created_at', [$startDate, $endDate])
            ->sum('message_count');

        $totalSessions = ChatSession::whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $conversionRate = $totalSessions > 0 ? 
            round(($this->getConversionCount($startDate, $endDate) / $totalSessions) * 100, 2) : 0;

        $avgResponseTime = $this->getAverageResponseTime($startDate, $endDate);

        $satisfaction = $this->getAverageSatisfaction($startDate, $endDate);

        $activeUsers = ChatSession::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        $newUsers = ChatSession::whereBetween('created_at', [$startDate, $endDate])
            ->where('message_count', '<=', 2)
            ->count();

        $retentionRate = $totalSessions > 0 ? 
            round((($totalSessions - $newUsers) / $totalSessions) * 100, 2) : 0;

        return [
            'totalMessages' => $totalMessages,
            'conversionRate' => $conversionRate,
            'avgResponseTime' => $avgResponseTime,
            'satisfaction' => $satisfaction,
            'activeUsers' => $activeUsers,
            'newUsers' => $newUsers,
            'retentionRate' => $retentionRate
        ];
    }

    private function getChartData($startDate, $endDate)
    {
        // Mensajes por hora
        $messagesData = ChatSession::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $messagesChart = [
            'labels' => $messagesData->pluck('hour')->map(function($hour) {
                return $hour . ':00';
            }),
            'data' => $messagesData->pluck('count')
        ];

        // Clics en botones (simulado)
        $buttonsChart = [
            'labels' => ['Productos', 'Envío', 'Pago', 'Contacto', 'Ofertas'],
            'data' => [45, 32, 28, 15, 22]
        ];

        return [
            'messages' => $messagesChart,
            'buttons' => $buttonsChart
        ];
    }

    private function getTopIntents($startDate, $endDate)
    {
        // Simular datos de intenciones (en producción, vendría de análisis real)
        return [
            ['name' => 'Consulta de Productos', 'count' => 156],
            ['name' => 'Información de Envío', 'count' => 89],
            ['name' => 'Métodos de Pago', 'count' => 67],
            ['name' => 'Ubicación', 'count' => 45],
            ['name' => 'Precios', 'count' => 38]
        ];
    }

    private function getPopularProducts($startDate, $endDate)
    {
        $products = GuineaPig::where('active', true)
            ->orderBy('views', 'desc')
            ->limit(5)
            ->get(['name', 'views']);

        return $products->map(function($product) {
            return [
                'name' => $product->name,
                'mentions' => $product->views ?? rand(10, 100)
            ];
        })->toArray();
    }

    private function getConversionCount($startDate, $endDate)
    {
        // Simular conteo de conversiones
        return ChatSession::whereBetween('created_at', [$startDate, $endDate])
            ->where('message_count', '>', 3)
            ->count();
    }

    private function getAverageResponseTime($startDate, $endDate)
    {
        // Simular tiempo de respuesta promedio en segundos
        return rand(15, 45);
    }

    private function getAverageSatisfaction($startDate, $endDate)
    {
        // Simular satisfacción promedio (1-5)
        return round(rand(40, 50) / 10, 1);
    }

    private function getRandomUserName()
    {
        $names = ['Juan Pérez', 'María García', 'Carlos López', 'Ana Martínez', 'Luis Rodríguez'];
        return $names[array_rand($names)];
    }

    private function getRandomAction()
    {
        $actions = [
            'preguntó por precios',
            'hizo clic en "Comprar"',
            'usó comando de voz',
            'vió fotos de productos',
            'solicitó información de envío',
            'preguntó por stock',
            'dejó feedback positivo'
        ];
        return $actions[array_rand($actions)];
    }

    private function getRandomType()
    {
        $types = ['consulta', 'conversión', 'interacción', 'feedback', 'navegación'];
        return $types[array_rand($types)];
    }

    public function getAdvancedMetrics()
    {
        $metrics = [
            'user_engagement' => $this->getUserEngagement(),
            'button_performance' => $this->getButtonPerformance(),
            'sentiment_analysis' => $this->getSentimentAnalysis(),
            'prediction_accuracy' => $this->getPredictionAccuracy(),
            'voice_usage' => $this->getVoiceUsageStats()
        ];

        return response()->json($metrics);
    }

    private function getUserEngagement()
    {
        return [
            'avg_session_duration' => '5:23',
            'messages_per_session' => 4.7,
            'button_click_rate' => 68.5,
            'voice_interaction_rate' => 23.4
        ];
    }

    private function getButtonPerformance()
    {
        return [
            'highest_ctr' => ['button' => '🐹 Ver Productos', 'ctr' => 89.2],
            'lowest_ctr' => ['button' => '📋 Ver Garantía', 'ctr' => 12.3],
            'avg_ctr' => 67.8,
            'conversion_by_button' => [
                '🛒 Comprar Ahora' => 34.5,
                '📦 Reservar' => 28.9,
                '💳 Ver Pagos' => 15.6
            ]
        ];
    }

    private function getSentimentAnalysis()
    {
        return [
            'positive' => 73.4,
            'neutral' => 21.2,
            'negative' => 5.4,
            'trend' => 'improving'
        ];
    }

    private function getPredictionAccuracy()
    {
        return [
            'intent_prediction' => 87.3,
            'next_question_prediction' => 72.1,
            'product_recommendation' => 91.5,
            'price_sensitivity' => 78.9
        ];
    }

    private function getVoiceUsageStats()
    {
        return [
            'voice_sessions' => 234,
            'voice_conversion_rate' => 45.6,
            'avg_voice_duration' => '12.3s',
            'most_used_commands' => [
                'precio' => 45,
                'envío' => 32,
                'stock' => 28,
                'comprar' => 19
            ]
        ];
    }
}
