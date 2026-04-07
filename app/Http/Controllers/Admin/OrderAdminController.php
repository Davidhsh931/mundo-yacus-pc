<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    /**
     * Mostrar todos los pedidos del sistema (Scope Global)
     * 
     * Contexto: Administrador (Gestión de Ventas)
     * - Retorna la colección completa de pedidos (cross-user)
     * - Permite gestión operativa del negocio
     * - Cambio de estados, conciliación de pagos y logística
     * - No filtra por usuario específico
     */
    public function index()
    {
        // Scope Global: Todos los pedidos del sistema
        $orders = Order::with(['user', 'items.guineaPig.images', 'items.guineaPig.seller'])
            ->latest()
            ->get();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'adminContext' => 'global' // Indica el contexto para el componente
        ]);
    }

    /**
     * Actualizar el estado de un pedido (administración global)
     * 
     * Permite a los administradores cambiar estados de cualquier pedido
     * para la gestión operativa del negocio
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,shipped,delivered,canceled'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        $order->update([
            'status' => $newStatus,
            'status_updated_at' => now(),
            'status_notes' => $request->notes ?? null
        ]);
        
        // Crear tracking entry
        \App\Models\OrderTracking::create([
            'order_id' => $order->id,
            'status' => $newStatus,
            'notes' => $request->notes ?? "Estado cambiado de {$oldStatus} a {$newStatus}",
            'updated_by' => auth()->id()
        ]);
        
        return back()->with('success', "Pedido #{$order->id} actualizado a: " . $this->getStatusName($newStatus));
    }
    
    private function getStatusName($status)
    {
        $statuses = [
            'pending' => 'Pendiente de Pago',
            'paid' => 'Pago Confirmado', 
            'shipped' => 'En Camino',
            'delivered' => 'Entregado',
            'canceled' => 'Cancelado'
        ];
        
        return $statuses[$status] ?? $status;
    }

    /**
     * Mostrar detalles de un pedido específico (administración)
     * 
     * Los administradores pueden ver cualquier pedido del sistema
     */
    public function show(Order $order)
    {
        // Scope Global: Cargar cualquier pedido sin restricciones de usuario
        $order->load(['user', 'items.guineaPig.images', 'items.guineaPig.seller']);
        
        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'adminContext' => 'global'
        ]);
    }
}