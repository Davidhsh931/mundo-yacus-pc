<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserAdminController extends Controller
{
    /**
     * Mostrar lista de usuarios con opciones de aprobación
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'currentUserEmail' => auth()->user()->email
        ]);
    }

    /**
     * Aprobar un administrador
     */
    public function approve(Request $request, User $user)
    {
        if ($user->role !== 'admin') {
            return back()->with('error', 'Solo se pueden aprobar administradores.');
        }

        $user->update(['is_approved' => true]);

        return back()->with('success', "Administrador {$user->name} aprobado correctamente.");
    }

    /**
     * Rechazar un administrador
     */
    public function reject(Request $request, User $user)
    {
        if ($user->role !== 'admin') {
            return back()->with('error', 'Solo se pueden rechazar administradores.');
        }

        $user->update(['is_approved' => false]);

        return back()->with('success', "Administrador {$user->name} rechazado correctamente.");
    }

    /**
     * Cambiar rol de usuario (solo superadmin)
     */
    public function changeRole(Request $request, User $user)
    {
        // Verificar que sea el superadmin
        if (auth()->user()->email !== 'admin@admin.com') {
            return back()->with('error', 'Solo el superadmin puede cambiar roles.');
        }

        $request->validate([
            'role' => 'required|in:cliente,admin'
        ]);

        // Extraer nombre de usuario actual
        $username = explode('@', $user->email)[0];

        // Cambiar email según rol
        if ($request->role === 'admin' && str_ends_with($user->email, '@cliente.com')) {
            // Cliente → Admin: cambiar @cliente.com a @admin.com
            $newEmail = $username . '@admin.com';

            // Verificar que el nombre de usuario no exista (con cualquier dominio)
            if (User::where('email', 'LIKE', $username . '@%')->where('id', '!=', $user->id)->exists()) {
                return back()->with('error', "El nombre de usuario '{$username}' ya está en uso.");
            }

            $user->update([
                'role' => 'admin',
                'is_approved' => false,
                'email' => $newEmail
            ]);

            return back()->with('success', "Rol de {$user->name} cambiado a admin y email actualizado a {$newEmail}.");
        } elseif ($request->role === 'cliente' && str_ends_with($user->email, '@admin.com')) {
            // Admin → Cliente: cambiar @admin.com a @cliente.com
            $newEmail = $username . '@cliente.com';

            // Verificar que el nombre de usuario no exista (con cualquier dominio)
            if (User::where('email', 'LIKE', $username . '@%')->where('id', '!=', $user->id)->exists()) {
                return back()->with('error', "El nombre de usuario '{$username}' ya está en uso.");
            }

            $user->update([
                'role' => 'cliente',
                'is_approved' => true,
                'email' => $newEmail
            ]);

            return back()->with('success', "Rol de {$user->name} cambiado a cliente y email actualizado a {$newEmail}.");
        } else {
            // Solo cambiar rol y aprobación
            $user->update([
                'role' => $request->role,
                'is_approved' => $request->role === 'admin' ? false : true
            ]);

            return back()->with('success', "Rol de {$user->name} cambiado a {$request->role} correctamente.");
        }
    }
}
