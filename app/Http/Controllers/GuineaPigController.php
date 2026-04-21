<?php

namespace App\Http\Controllers;

use App\Models\GuineaPig;
use App\Models\GuineaPigImage;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GuineaPigController extends Controller
{
    public function index()
    {
        try {
            // Log the start
            \Log::info('GuineaPigController::index() started');

            // Traemos productos activos con imágenes y vendedores
            \Log::info('Loading guinea pigs...');
            $guineaPigs = \App\Models\GuineaPig::with(['seller', 'images', 'category'])
                ->where('active', true)
                ->orderBy('created_at', 'desc')
                ->get();
            \Log::info('Loaded ' . count($guineaPigs) . ' guinea pigs');

            // Traemos todas las categorías disponibles
            \Log::info('Loading categories...');
            $categories = \App\Models\Category::withCount('guineaPigs')
                ->orderBy('name', 'asc')
                ->get();
            \Log::info('Loaded ' . count($categories) . ' categories');

            // Agregar timestamp para evitar cache
            $timestamp = now()->timestamp;

            \Log::info('Rendering Home view');
            return Inertia::render('Home', [
                'guineaPigs' => $guineaPigs,
                'categories' => $categories,
                'cacheBuster' => $timestamp
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in GuineaPigController::index(): ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e; // Re-throw to see the error
        }
    }

    public function productsByCategory(Request $request)
    {
        // Obtener el ID de la categoría desde la query string
        $categoryId = $request->query('category');
        
        // Obtener la categoría para mostrar información
        $category = null;
        if ($categoryId) {
            $category = \App\Models\Category::withCount('guineaPigs')->find($categoryId);
        }
        
        // Construir la consulta base
        $query = \App\Models\GuineaPig::with(['seller', 'images', 'category'])
            ->where('active', true);
        
        // Aplicar filtro por categoría si existe
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Obtener productos
        $guineaPigs = $query->orderBy('created_at', 'desc')->get();
        
        // Obtener todas las categorías para el filtro
        $categories = \App\Models\Category::withCount('guineaPigs')
            ->orderBy('name', 'asc')
            ->get();
        
        return Inertia::render('Products', [
            'guineaPigs' => $guineaPigs,
            'categories' => $categories,
            'selectedCategory' => $category,
            'categoryId' => $categoryId
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
    // Obtenemos ventas reales de los últimos 30 días para este producto
    $ventas = OrderItem::where('guinea_pig_id', $id)
        ->where('created_at', '>=', now()->subDays(30))
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(quantity) as total'))
        ->groupBy('date')
        ->get()
        ->pluck('total', 'date');

    $values = [];
    $labels = [];
    
    foreach(range(0, 29) as $i) {
        $fecha = now()->subDays(29 - $i)->format('Y-m-d');
        $labels[] = now()->subDays(29 - $i)->day;
        $values[] = $ventas[$fecha] ?? 0; // Si no hay ventas, es 0
    }

    $totalVentas = array_sum($values);
    $sugerenciaStock = $totalVentas > 0 ? ceil($totalVentas / 4) : 5; // Sugiere el 25% de las ventas mensuales

    return response()->json([
        'values' => $values,
        'labels' => $labels,
        'sugerencia' => $sugerenciaStock,
        'total_ventas' => $totalVentas,
        'promedio_diario' => $totalVentas > 0 ? round($totalVentas / 30, 2) : 0
    ]);
}

    public function store(Request $request) 
{
    // Debug: Ver qué datos llegan
    \Log::info('Datos recibidos en store:', $request->all());
    
    $request->validate([
        'name'          => 'required|string|max:255',
        'description'   => 'required|string',
        'price'         => 'required|numeric',
        'stock'         => 'required|integer|min:0',
        'species'       => 'required',
        'product_state' => 'required',
        'image'         => 'required|image|max:5120', // Aumentado a 5MB para Railway
        'specifications' => 'nullable', 
    ]);

    try {
        // --- 🤖 FASE 1: CLASIFICACIÓN INTELIGENTE (IA) ---
        $categories = Category::all();
        $fallbackCategoryId = 3; // ID para 'Otros'
        $categoryId = $fallbackCategoryId;

        if (env('OPENAI_API_KEY')) {
            $rules = $categories->map(fn($c) => "- ID {$c->id}: {$c->training_data}")->implode("\n");
            
            $prompt = "Eres un clasificador experto de productos para 'Mundo Yacus'.
            Analiza el producto y responde ÚNICAMENTE el número del ID de categoría que le corresponde.
            
            REGLAS:
            {$rules}
            
            PRODUCTO:
            Nombre: '{$request->name}'
            Descripción: '{$request->description}'
            Contexto: '{$request->ai_context}'
            
            REGLA DE ORO: Responde SOLO el número del ID.";

            try {
                $response = Http::withToken(env('OPENAI_API_KEY'))
                    ->timeout(5) // Para que no bloquee tu app si la API tarda
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'temperature' => 0,
                    ]);

                if ($response->successful()) {
                    $aiResponse = trim($response->json()['choices'][0]['message']['content'] ?? '');
                    $predictedId = (int) $aiResponse;
                    
                    // Validamos que el ID entregado por la IA realmente exista en nuestra DB
                    if ($categories->pluck('id')->contains($predictedId)) {
                        $categoryId = $predictedId;
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("IA Falló: Usando fallback - " . $e->getMessage());
            }
        }

        // --- 🛠️ FASE 2: PROCESAMIENTO DE DATOS ---
        $specs = $request->specifications;
        if (is_string($specs)) {
            $specs = json_decode($specs, true);
        }

        // --- FASE 3: CREACIÓN Y GUARDADO ---
        $pig = \App\Models\GuineaPig::create([
            'user_id'         => auth()->id() ?? 1,
            'name'            => $request->name,
            'description'     => $request->description,
            'category_id'     => $categoryId, // ID ASIGNADO POR IA O FALLBACK
            'species'         => $request->species,
            'price'           => $request->price,
            'product_state'   => $request->product_state,
            'stock'           => $request->stock, 
            'active'          => true,
            'specifications'  => $specs,
            'ia_verification' => json_encode([
                'status' => 'automatic',
                'confidence' => 'high',
                'date' => now()->toDateTimeString()
            ]), 
        ]);

        // --- 🖼️ FASE 4: GESTIÓN DE IMÁGENES PERSISTENTE ---
        if ($request->hasFile('image')) {
            // Guardamos en 'productos' que está vinculado a tu volumen de Railway
            $path = $request->file('image')->store('productos', 'public');

            \App\Models\GuineaPigImage::create([
                'guinea_pig_id' => $pig->id,
                'image_path'    => $path,
                'position'      => 0
            ]);
        }

        return redirect()->route('products.show', $pig->id)->with('message', '¡Publicado y clasificado con éxito!');

    } catch (\Exception $e) {
        \Log::error("Error crítico en store: " . $e->getMessage());
        return back()->with('error', 'Ocurrió un error al procesar el producto.');
    }
}

    public function getSalesLast30Days()
    {
        $salesByDate = OrderItem::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(quantity * unit_price) as total')
            )
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        $labels = [];
        $data   = [];

        foreach (range(0, 29) as $i) {
            $date     = now()->subDays(29 - $i)->format('Y-m-d');
            $labels[] = $date;
            $data[]   = isset($salesByDate[$date]) ? (float) $salesByDate[$date] : 0;
        }

        return response()->json([
            'labels' => $labels,
            'data'   => $data,
        ]);
    }
}