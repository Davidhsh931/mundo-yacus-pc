<?php

namespace App\Http\Controllers;

use App\Models\GuineaPig;
use App\Models\GuineaPigImage;
use App\Models\OrderItem; // Añadimos el modelo para mejor legibilidad
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GuineaPigController extends Controller
{
    public function index()
    {
        // Traemos productos activos con imágenes y vendedores
        $guineaPigs = \App\Models\GuineaPig::with(['seller', 'images'])
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Traemos eventos activos ordenados por fecha
        $events = \App\Models\Event::active()
            ->upcoming()
            ->orderBy('event_date', 'asc')
            ->take(3) // Limitamos a 3 eventos para la home
            ->get();
        
        // Agregar accesores a cada evento
        $events->each(function ($event) {
            $event->formatted_date = $event->formatted_date;
            $event->image_url = $event->image_url;
        });

        // Agregar timestamp para evitar cache
        $timestamp = now()->timestamp;

        return Inertia::render('Home', [
            'guineaPigs' => $guineaPigs,
            'events' => $events,
            'cacheBuster' => $timestamp // Forzar recarga de frontend
        ]);
    }

    public function show($id)
    {
        // Buscamos el animal con sus imágenes y vendedor
        $pig = GuineaPig::with(['images', 'seller'])->findOrFail($id);
        
        // IMPORTANTE: El nombre aquí debe ser 'pig' para que coincida con web.php y Vue
        return Inertia::render('Product', [
            'pig' => $pig 
        ]);
    }

    public function uploadImage(Request $request, $id)
{
    $request->validate(['image' => 'required|image|max:2048']);
    $pig = GuineaPig::findOrFail($id);
    
    // Esto guarda en storage/app/public/images y devuelve "images/archivo.jpg"
    $path = $request->file('image')->store('images', 'public');

    GuineaPigImage::create([
        'guinea_pig_id' => $pig->id,
        // Guardamos solo el path relativo
        'image_path' => $path, 
        'position' => $pig->images()->count() + 1
    ]);

    return back()->with('success', 'Imagen subida correctamente');
}

    /**
     * Esta es la función "Cerebro" que conecta con Python
     */
    public function sugerirStock($id) 
{
    // TODO: Implementar lógica real de sugerencia de stock basada en ventas históricas
    // Por ahora, devolver valores simulados para que la gráfica funcione
    
    $values = [];
    $labels = [];
    
    foreach(range(0, 29) as $i) {
        // Generar valores simulados de ventas
        $values[] = rand(5, 25);
        $labels[] = (string)now()->subDays(29 - $i)->day;
    }

    return response()->json([
        'values' => $values,
        'labels' => $labels
    ]);
}

    public function store(Request $request) 
{
    $request->validate([
        'name'          => 'required|string|max:255',
        'price'         => 'required|numeric',
        'stock'         => 'required|integer|min:0',
        'species'       => 'required',
        'product_state' => 'required',
        'image'         => 'required|image|max:2048', 
        // 👇 AGREGAMOS ESTA LÍNEA PARA QUE LARAVEL NO SE QUEJE
        'specifications' => 'nullable', 
    ]);

    try {
        // 1. Procesamos las especificaciones ANTES de crear
        // Si el Frontend envía un JSON string, lo decodificamos para que el 
        // Cast del modelo ('array') lo maneje correctamente al guardar.
        $specs = $request->specifications;
        if (is_string($specs)) {
            $specs = json_decode($specs, true);
        }

        // 2. Creamos el animal
        $pig = \App\Models\GuineaPig::create([
            'user_id'         => auth()->id() ?? 1, // Forzar a 1 si no hay sesión
            'name'            => $request->name,
            'species'         => $request->species,
            'price'           => $request->price,
            'product_state'   => $request->product_state,
            'stock'           => $request->stock, 
            'active'          => true,
            'specifications'  => $specs, // <--- Usamos la variable procesada
            'ia_verification' => $request->ia_verification, 
        ]);

        // 3. Guardamos la imagen (esto ya está bien)
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');

            \App\Models\GuineaPigImage::create([
                'guinea_pig_id' => $pig->id,
                'image_path'    => $path,
                'position'      => 0 // Mejor empezar en 0
            ]);
        }

        // Redirigimos al producto creado para verlo inmediatamente
        return redirect()->route('products.show', $pig->id)->with('message', '¡Publicado con éxito!');

    } catch (\Exception $e) {
        \Log::error("Error al crear GuineaPig: " . $e->getMessage());
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}
}