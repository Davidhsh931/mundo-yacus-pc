<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuineaPig;
use App\Models\GuineaPigImage;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GuineaPigAdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/GuineaPigs/Index', [
            'guineaPigs' => GuineaPig::with(['images', 'seller', 'category'])
                ->orderBy('created_at', 'desc')
                ->get(),
            'trashedPigs' => GuineaPig::onlyTrashed()
                ->with(['images'])
                ->orderBy('deleted_at', 'desc')
                ->get(),
            'lowStockCount' => GuineaPig::where('stock', '<', 5)
                ->where('active', true)
                ->count()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/CreateProduct', [
            'categories' => Category::orderBy('name')->get()
        ]); 
    }

    public function store(Request $request)
    {
        // 1. Mantener datos tal como llegan - no convertir a null
        $data = $request->all();

        try {
            // 2. Validar datos del formulario
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'breed' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,bmp,tiff|max:2048',
                'specifications' => 'nullable|string'
            ]);

            // Obtener categorías disponibles dinámicamente
            $categories = Category::all();
            
            // Fallback automático: usar la primera categoría disponible
            $fallbackCategoryId = $categories->first()->id ?? 1;
            $categoryId = $request->category_id ?? $fallbackCategoryId;

            $specificationsArray = json_decode($request->specifications, true) ?: [];

            // 3. Forzar valores si están vacíos
            $description = !empty($request->description) ? $request->description : 'Producto de alta calidad';
            $product_state = !empty($request->product_state) ? $request->product_state : 'Disponible';
            
            // 3. Intenta crear el registro
            $guineaPig = GuineaPig::create([
                'name' => $request->name,
                'description' => $description,
                'category_id' => $categoryId, 
                'price' => $request->price,
                'stock' => $request->stock,
                'product_state' => $product_state,
                'breed' => $request->breed ?? 'No especificada',
                'average_weight' => 0,
                'specifications' => $specificationsArray,
                'ia_verification' => json_encode([
                    'status' => 'manual',
                    'confidence' => 'n/a',
                    'date' => now()
                ])
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('images', $imageName, 'public');
                
                GuineaPigImage::create([
                    'guinea_pig_id' => $guineaPig->id,
                    'image_path' => $imagePath,
                ]);
            }

            return redirect()->route('guinea-pigs.index');

        } catch (\Exception $e) {
            // 4. Si falla, esto capturará el error real en los logs
            \Log::error("Error al crear producto en Mundo Yacus: " . $e->getMessage());
            return response()->json([
                'error' => 'Error interno',
                'detail' => $e->getMessage() // OJO: Quita esto antes de que los clientes lo vean
            ], 500);
        }
    }

    public function edit($id)
    {
        $pig = GuineaPig::with('images')->findOrFail($id);
        return Inertia::render('Admin/EditPig', [
            'pig' => $pig,
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function update(Request $request, $id) 
    {
        $pig = GuineaPig::findOrFail($id);

        // 1. Mapeo manual de campos (Para que coincidan con tu BD)
        $pig->name = $request->name;
        $pig->breed = $request->breed; 
        $pig->price = $request->price;
        $pig->stock = $request->stock;
        $pig->active = $request->active == "1" ? true : false;
        $pig->description = $request->description;
        $pig->category_id = $request->category_id ?? $pig->category_id;
        
        // 2. Manejo de Specifications (Decodificar el JSON que llega como string)
        $pig->specifications = json_decode($request->specifications, true);

        // 3. Manejo de Imagen (Si llega una nueva)
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            
            // Actualizar o crear la relación de imagen
            $pig->images()->updateOrCreate(
                ['guinea_pig_id' => $pig->id],
                ['image_path' => $path]
            );
        }

        // 4. Guardado final
        $pig->save();

        return redirect("/product/{$id}")->with('success', '¡Actualizado con éxito!');
    }

    public function destroy($id)
    {
        $pig = GuineaPig::findOrFail($id);
        $pig->delete();
        return redirect()->back();
    }

    public function restore($id)
    {
        $pig = GuineaPig::onlyTrashed()->findOrFail($id);
        $pig->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $pig = GuineaPig::onlyTrashed()->with('images')->findOrFail($id);
        foreach ($pig->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }
        $pig->forceDelete();
        return redirect()->back();
    }

    public function create_coment()
    {
        return Inertia::render('Admin/CreatePigComent'); 
    }
}