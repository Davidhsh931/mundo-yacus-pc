<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuineaPig;
use App\Models\SuggestedCategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoCategoryController extends Controller
{
    public function index()
    {
        $suggestedCategories = SuggestedCategory::where('status', 'pending')->get();
        return response()->json($suggestedCategories);
    }

    public function generateGroups()
    {
        if (!env('OPENAI_API_KEY')) {
            return response()->json(['error' => 'OPENAI_API_KEY no configurada'], 400);
        }

        // Obtener todos los productos
        $products = GuineaPig::all();
        
        // Construir prompt para agrupamiento
        $productsText = $products->map(function ($p) {
            return "ID: {$p->id}, Nombre: {$p->name}, Descripción: {$p->description}, Especie: {$p->species}";
        })->implode("\n");

        $prompt = "Eres un experto en agrupar productos para 'Mundo Yacus' (marketplace de productos agrícolas y animales).
        
Analiza los siguientes productos y agrúpalos en categorías lógicas basadas en similitudes.
Responde ÚNICAMENTE en formato JSON con esta estructura:
{
  \"groups\": [
    {
      \"name\": \"Nombre del grupo\",
      \"description\": \"Descripción breve\",
      \"product_ids\": [1, 2, 3]
    }
  ]
}

PRODUCTOS:
{$productsText}

REGLAS:
- Crea entre 3-6 grupos
- Cada grupo debe tener al menos 2 productos
- Los nombres deben ser descriptivos y cortos (máximo 3 palabras)
- Responde SOLO el JSON, sin texto adicional.";

        try {
            $response = Http::withToken(env('OPENAI_API_KEY'))
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.3,
                ]);

            if ($response->successful()) {
                $aiResponse = $response->json()['choices'][0]['message']['content'] ?? '';
                $groups = json_decode($aiResponse, true);

                if (isset($groups['groups'])) {
                    // Limpiar sugerencias anteriores
                    SuggestedCategory::where('status', 'pending')->delete();

                    // Crear nuevas sugerencias
                    foreach ($groups['groups'] as $group) {
                        SuggestedCategory::create([
                            'name' => $group['name'],
                            'description' => $group['description'] ?? null,
                            'product_ids' => $group['product_ids'] ?? [],
                            'status' => 'pending',
                        ]);
                    }

                    return response()->json(['success' => true, 'groups_count' => count($groups['groups'])]);
                }
            }

            return response()->json(['error' => 'Error al procesar respuesta de IA'], 500);
        } catch (\Exception $e) {
            Log::error("Error en agrupamiento automático: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        $suggested = SuggestedCategory::findOrFail($id);
        
        // Crear categoría real
        $category = Category::create([
            'name' => $suggested->name,
            'description' => $suggested->description,
            'training_data' => '', // Se puede llenar después
        ]);

        // Asignar productos a la nueva categoría
        GuineaPig::whereIn('id', $suggested->product_ids)
            ->update(['category_id' => $category->id]);

        // Marcar como aprobada
        $suggested->update(['status' => 'approved']);

        return response()->json(['success' => true, 'category_id' => $category->id]);
    }

    public function reject($id)
    {
        $suggested = SuggestedCategory::findOrFail($id);
        $suggested->update(['status' => 'rejected']);

        return response()->json(['success' => true]);
    }
}
