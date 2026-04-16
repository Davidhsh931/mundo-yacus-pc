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
        // Obtener parámetros de búsqueda y filtros
        $categoryId = $request->query('category');
        $search = $request->query('search');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $sortBy = $request->query('sort', 'created_at');
        $sortOrder = $request->query('order', 'desc');

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

        // Aplicar búsqueda por nombre o raza (sin descripción para evitar coincidencias falsas)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('breed', 'like', '%' . $search . '%');
            });
        }

        // Aplicar filtro de precio mínimo
        if ($minPrice) {
            $query->where('price', '>=', $minPrice);
        }

        // Aplicar filtro de precio máximo
        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice);
        }

        // Aplicar ordenamiento
        $allowedSortFields = ['created_at', 'price', 'name'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Obtener productos
        $guineaPigs = $query->get();

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
            'categoryId' => $categoryId,
            'search' => $search,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ]);
    }
}
