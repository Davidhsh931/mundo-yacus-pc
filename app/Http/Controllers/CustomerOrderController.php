<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    /**
     * Mostrar los pedidos del cliente autenticado (Scope de Usuario)
     * 
     * Contexto: Cliente (Mis Pedidos)
     * - Filtra pedidos estrictamente por Auth::id()
     * - Un administrador que compra ve solo sus pedidos personales
     * - No se mezcla con la administración global
     */
    public function index()
    {
        $user = Auth::user();
        
        // Scope de Usuario: Solo pedidos del cliente autenticado
        $orders = Order::where('user_id', $user->id)
            ->with(['items.guineaPig.images', 'items.guineaPig.seller'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders,
            'userContext' => 'customer' // Indica el contexto para el componente
        ]);
    }
    
    /**
     * Mostrar detalles de un pedido específico del cliente
     * 
     * Verifica que el pedido pertenezca al usuario autenticado
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        
        // Verificar que el pedido pertenezca al usuario (Scope de Usuario)
        if ($order->user_id !== $user->id) {
            abort(403, 'No tienes permiso para ver este pedido.');
        }
        
        // Cargar detalles completos del pedido con tracking
        $order->load(['items.guineaPig.images', 'items.guineaPig.seller', 'trackings.updatedBy']);
        
        // Forzar carga de guineaPig para cada item (solución temporal)
        $order->items->each(function($item) {
            $item->guineaPig = \App\Models\GuineaPig::with(['images', 'seller'])->find($item->guinea_pig_id);
        });
        
        // Cargar configuraciones dinámicas y limpiar números
        $whatsappNumber = \App\Models\Setting::get('whatsapp_number', '51987654321');
        $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber); // Solo números
        
        $settings = [
            'whatsappNumber' => $whatsappNumber,
            'timestamp' => time(), // Forzar recarga de Vue
        ];
        
        return Inertia::render('Customer/Orders/Show', array_merge([
            'order' => $order,
            'userContext' => 'customer',
            'trackings' => $order->trackings ?? []
        ], $settings));
    }
    
    /**
     * Cancelar un pedido del cliente
     * 
     * Solo permite cancelar pedidos propios y en estado apropiado
     */
    public function cancel(Request $request, Order $order)
    {
        $user = Auth::user();
        
        // Verificar que el pedido pertenezca al usuario (Scope de Usuario)
        if ($order->user_id !== $user->id) {
            abort(403, 'No tienes permiso para cancelar este pedido.');
        }
        
        // Verificar que el pedido pueda ser cancelado
        if (!in_array($order->status, ['pending', 'paid'])) {
            return back()->with('error', 'Este pedido ya no puede ser cancelado.');
        }
        
        $order->update(['status' => 'canceled']);
        
        return back()->with('success', 'Pedido cancelado correctamente.');
    }
}
