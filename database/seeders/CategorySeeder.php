<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usamos un bucle para que el código sea más limpio
        $categories = [
            [
                'id' => 1,
                'name' => 'Animal',
                'description' => 'Animales vivos para cría o mascota',
                'training_data' => 'vivo, mascota, cría, reproductor, animal, cuy vivo'
            ],
            [
                'id' => 2,
                'name' => 'Carne',
                'description' => 'Carne procesada lista para consumo',
                'training_data' => 'carne, beneficiado, procesado, comida, lista para cocinar, filete'
            ],
            [
                'id' => 3,
                'name' => 'Otros',
                'description' => 'Alimentos, herramientas, medicinas y accesorios',
                'training_data' => 'alimento, forraje, alfalfa, jaula, medicina, herramienta, insumo'
            ],
        ];

        foreach ($categories as $category) {
    // Esto busca el ID incluyendo los que fueron borrados lógicamente
    \App\Models\Category::withTrashed()->updateOrCreate(
        ['id' => $category['id']], 
        $category
    );
}
    }
}
