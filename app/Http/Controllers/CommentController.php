<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Guardar un comentario nuevo (Solo Clientes/Admin logueados)
    public function store(Request $request)
    {
        $user = auth()->user();

        // 1. Validaciones básicas
        $request->validate([
            'guinea_pig_id' => 'required|exists:guinea_pigs,id',
            'content'       => 'required|string|min:3|max:500',
            'rating'        => 'integer|min:1|max:5'
        ]);

        // 2. LA REGLA DE ORO: ¿Ha comprado este producto?
        // Buscamos en la tabla de órdenes del usuario
        $hasPurchased = \App\Models\Order::where('user_id', $user->id)
            ->whereIn('status', ['paid', 'shipped', 'delivered']) // 'paid' (Pagado), 'shipped' (Enviado), 'delivered' (Entregado)
            ->whereHas('items', function ($query) use ($request) {
                $query->where('guinea_pig_id', $request->guinea_pig_id);
            })->exists();

        // 3. Si es Admin, lo dejamos pasar siempre (para pruebas o respuestas oficiales)
        // Pero si es cliente y NO ha comprado, lo rebotamos.
        if ($user->role !== 'admin' && !$hasPurchased) {
            return back()->with('error', 'Solo puedes comentar productos que hayas comprado.');
        }

        // 4. Si pasa la prueba, guardamos
        $comment = Comment::create([
            'user_id'       => $user->id,
            'guinea_pig_id' => $request->guinea_pig_id,
            'content'       => $request->content,
            'rating'        => $request->rating ?? 5,
        ]);

        // 5. NOTIFICACIÓN AUTOMÁTICA PARA ADMIN
        // Buscar todos los usuarios admin
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => "💬 Nuevo comentario en producto #{$request->guinea_pig_id}",
                'type' => 'comment',
                'read' => false,
                'data' => json_encode([
                    'product_id' => $request->guinea_pig_id,
                    'comment_id' => $comment->id,
                    'user_name' => $user->name
                ])
            ]);
        }

        return back()->with('message', '¡Gracias por tu reseña verificada!');
    }

    // Borrar un comentario (Dueño o Admin)
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $user = Auth::user();

        // SEGURIDAD: Solo el autor o el administrador pueden borrar
        if ($user->id === $comment->user_id || $user->role === 'admin') {
            $comment->delete();
            return back()->with('message', 'Comentario eliminado.');
        }

        return back()->with('error', 'No tienes permiso para borrar esto.');
    }
}
