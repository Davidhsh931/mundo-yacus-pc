<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items.guineaPig.images'])
                    ->latest()
                    ->get();
        
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,shipped,delivered,canceled'
        ]);

        $order->update(['status' => $request->status]);
        
        return back()->with('success', 'Estado del pedido actualizado correctamente.');
    }
}
