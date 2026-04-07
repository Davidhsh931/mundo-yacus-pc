<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Obtener todas las notificaciones del usuario autenticado
     */
    public function index(): JsonResponse
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                $data = json_decode($notification->data, true) ?? [];
                
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'read' => $notification->read,
                    'created_at' => $notification->created_at->toISOString(),
                    'order_id' => $data['order_id'] ?? null,
                    'product_id' => $data['product_id'] ?? null,
                    'comment_id' => $data['comment_id'] ?? null,
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Método de prueba para debugging (sin auth)
     */
    public function test(): JsonResponse
    {
        $notifications = Notification::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                $data = json_decode($notification->data, true) ?? [];
                
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'read' => $notification->read,
                    'created_at' => $notification->created_at->toISOString(),
                    'order_id' => $data['order_id'] ?? null,
                    'product_id' => $data['product_id'] ?? null,
                    'comment_id' => $data['comment_id'] ?? null,
                    'data_raw' => $notification->data, // Para debugging
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead($id): JsonResponse
    {
        $notification = Notification::where('user_id', auth()->id())
            ->findOrFail($id);
        
        $notification->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída',
            'notification_id' => $id
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllRead(): JsonResponse
    {
        Notification::where('user_id', auth()->id())
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }
}
