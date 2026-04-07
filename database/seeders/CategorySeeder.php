<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Category::create([
            'name' => 'Animal Vivo',
            'description' => 'Animales vivos para cría o mascota',
            'training_data' => 'vivo, mascota, cría, reproductor, animal, cuy vivo'
        ]);
        \App\Models\Category::create([
            'name' => 'Carne Beneficiada',
            'description' => 'Carne procesada lista para consumo',
            'training_data' => 'carne, beneficiado, procesado, comida, lista para cocinar, filete'
        ]);
        \App\Models\Category::create([
            'name' => 'Otros/Insumos',
            'description' => 'Alimentos, herramientas, medicinas y accesorios',
            'training_data' => 'alimento, forraje, alfalfa, jaula, medicina, herramienta, insumo'
        ]);
    }
}
