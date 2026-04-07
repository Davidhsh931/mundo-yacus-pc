<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 🛑 GUARDIÁN: Si no está autenticado o no es admin, lo expulsa
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Acceso denegado: Área exclusiva para administradores.');
        }

        // ✅ Si es admin, deja pasar
        return $next($request);
    }
}
