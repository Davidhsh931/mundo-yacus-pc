<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::latest()->get();
        
        // Agregar accesor formatted_date a cada evento
        $events->each(function ($event) {
            $event->formatted_date = $event->formatted_date;
        });
        
        // Obtener configuración del banner
        $bannerText = \App\Models\Setting::get('banner_text', '');
        $bannerActive = \App\Models\Setting::get('banner_active', '0');
        
        return Inertia::render('Admin/Events/Index', [
            'events' => $events,
            'banner_text' => $bannerText,
            'banner_active' => $bannerActive === '1'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'image' => 'nullable|image|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        Event::create($validated);

        return back()->with('success', 'Noticia publicada con éxito 🐹');
    }

    public function destroy(Event $event)
    {
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        $event->delete();
        return back();
    }

    public function updateSettings(Request $request)
    {
        // Guardamos o actualizamos el texto del banner
        \App\Models\Setting::updateOrCreate(['key' => 'banner_text'], ['value' => $request->banner_text]);
        
        // Guardamos si está activo (1 o 0)
        \App\Models\Setting::updateOrCreate(['key' => 'banner_active'], ['value' => $request->banner_active ? '1' : '0']);

        return back()->with('success', 'Configuración de anuncios actualizada 📢');
    }
}
