<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Mostrar todas las configuraciones
     */
    public function index()
    {
        $settings = Setting::orderBy('key', 'asc')->get();
        
        return Inertia::render('Admin/Settings', [
            'settings' => $settings
        ]);
    }

    /**
     * Guardar configuraciones
     */
    public function update(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string|max:20',
            'business_name' => 'required|string|max:100',
            'yape_number' => 'required|string|max:20',
            'plin_number' => 'required|string|max:20',
            'currency_symbol' => 'required|string|max:5',
            'enable_whatsapp' => 'required|boolean',
        ]);

        // Actualizar cada configuración
        foreach ($request->except(['_method', '_token']) as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', '✅ Configuraciones guardadas exitosamente');
    }

    /**
     * Obtener configuración específica para uso en frontend
     */
    public function getPublicSettings()
    {
        return response()->json([
            'whatsapp_number' => Setting::get('whatsapp_number', '51987654321'),
            'business_name' => Setting::get('business_name', 'Mundo Yacus'),
            'yape_number' => Setting::get('yape_number', '987654321'),
            'plin_number' => Setting::get('plin_number', '987654321'),
            'currency_symbol' => Setting::get('currency_symbol', 'S/'),
            'enable_whatsapp' => Setting::get('enable_whatsapp', '1') === '1',
        ]);
    }
}
