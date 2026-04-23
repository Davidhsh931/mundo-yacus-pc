<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\GuineaPig;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Mostrar dashboard con estadísticas reales
     */
    public function index()
    {
        // Estadísticas generales
        $totalPigs = GuineaPig::count();
        $totalOrders = Order::count();
        $totalClients = User::where('role', 'cliente')->count();
        
        // Datos para el gráfico de ventas (últimos 30 días)
        $salesData = $this->getSalesData();
        
        // Stock bajo
        $lowStockCount = GuineaPig::where('stock', '<', 5)->where('active', true)->count();
        
        // Productos recientes
        $recentProducts = GuineaPig::with(['images', 'seller'])
            ->latest()
            ->take(5)
            ->get();
        
        return Inertia::render('Admin/Dashboard', [
            'totalPigs' => $totalPigs,
            'totalOrders' => $totalOrders,
            'totalClients' => $totalClients,
            'stats' => [
                'stock_sugerido' => $this->calculateSuggestedStock($salesData['values']),
                'chart_data' => $salesData
            ],
            'lowStockCount' => $lowStockCount,
            'recentProducts' => $recentProducts,
            'categories' => Category::orderBy('name')->get()
        ]);
    }
    
    /**
     * Obtener datos de ventas para el gráfico
     */
    private function getSalesData()
    {
        // Generar datos de ejemplo para los últimos 30 días
        $values = [];
        $labels = [];
        
        for ($i = 29; $i >= 0; $i--) {
            // Generar valor aleatorio de ventas (simulación)
            $ventas = rand(0, 15);
            $values[] = $ventas;
            
            // Etiqueta del día
            $day = now()->subDays($i)->day;
            $labels[] = (string)$day;
        }
        
        return [
            'values' => $values,
            'labels' => $labels
        ];
    }
    
    /**
     * Calcular stock sugerido basado en ventas
     */
    private function calculateSuggestedStock($salesValues)
    {
        $totalSales = array_sum($salesValues);
        
        if ($totalSales > 0) {
            return (string)ceil($totalSales * 1.2);
        }
        
        return 'Calculando...';
    }
}
