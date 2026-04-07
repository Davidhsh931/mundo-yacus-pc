<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiTrainingController extends Controller
{
    public function index()
    {
        // Categorías activas (ordenadas)
        $categories = Category::orderBy('name', 'asc')->get();
        // Categorías en la papelera (ordenadas)
        $trashed = Category::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        
        return Inertia::render('Admin/AiTraining', [
            'categories' => $categories,
            'trashed' => $trashed
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name|max:255',
        ]);

        Category::create([
            'name' => $request->name,
            'training_data' => 'palabra clave inicial', // Un valor por defecto
        ]);

        return back()->with('message', '¡Nueva categoría creada!');
    }

    public function update(Request $request, Category $category)
    {
        $category->update([
            'name' => $request->name,
            'training_data' => $request->training_data
        ]);

        return back()->with('message', '¡Categoría actualizada!');
    }

    public function destroy(Category $category)
    {
        $category->delete(); // Se va a la papelera
        return back()->with('message', 'Categoría movida a la papelera');
    }

    public function restore($id)
    {
        Category::withTrashed()->findOrFail($id)->restore();
        return back()->with('message', 'Categoría restaurada');
    }

    public function forceDelete($id)
    {
        // Buscamos solo en los eliminados (papelera)
        $category = Category::onlyTrashed()->findOrFail($id);
        
        // Borrado físico de la base de datos
        $category->forceDelete(); 

        return back()->with('message', 'Categoría eliminada permanentemente.');
    }
}
