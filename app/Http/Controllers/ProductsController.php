<?php

namespace App\Http\Controllers;

use App\Models\GuineaPig;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductsController extends Controller
{
    public function index(Request $request)
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
        
        // Obtener eventos (mantener consistencia con la home)
        $events = \App\Models\Event::active()
            ->upcoming()
            ->orderBy('event_date', 'asc')
            ->take(3)
            ->get();
        
        $events->each(function ($event) {
            $event->formatted_date = $event->formatted_date;
            $event->image_url = $event->image_url;
        });
        
        return Inertia::render('Products', [
            'guineaPigs' => $guineaPigs,
            'categories' => $categories,
            'selectedCategory' => $category,
            'events' => $events,
            'categoryId' => $categoryId
        ]);
    }
}
