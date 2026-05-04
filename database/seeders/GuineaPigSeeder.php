<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GuineaPig;
use App\Models\Category;
use App\Models\User;

class GuineaPigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener categorías existentes o crearlas
        $categoryAnimal = Category::firstOrCreate(['name' => 'Animales']);
        $categoryAlfalfa = Category::firstOrCreate(['name' => 'Alfalfas']);
        $categoryAccesorios = Category::firstOrCreate(['name' => 'Accesorios']);
        
        // Obtener un usuario vendedor
        $seller = User::where('role', 'admin')->first() ?? User::first();

        // Productos profesionales de Cuyes
        $products = [
            [
                'name' => 'Cuy Peruano Premium - Línea Andina',
                'description' => 'Cuy de raza peruana de alta calidad, criado en condiciones óptimas en la región de Huánuco. Ideal para reproducción y consumo familiar. Peso promedio: 1.2-1.5 kg. Alimentación 100% natural con alfalfa y balanceados.',
                'breed' => 'Peruano',
                'average_weight' => 1.3,
                'category_id' => $categoryAnimal->id,
                'species' => 'Cavia porcellus',
                'price' => 45.00,
                'stock' => 25,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Edad' => '3-4 meses',
                    'Peso' => '1.2-1.5 kg',
                    'Género' => 'Mixto',
                    'Vacunado' => 'Sí',
                    'Certificado' => 'Disponible'
                ],
                'active' => true,
                'user_id' => $seller->id
            ],
            [
                'name' => 'Cuy Mejorado - Línea Genética',
                'description' => 'Cuy mejorado genéticamente para mayor rendimiento en carne. Criado con técnicas modernas de reproducción. Excelente conversión alimenticia. Peso promedio: 1.5-1.8 kg. Certificado sanitario incluido.',
                'breed' => 'Mejorado',
                'average_weight' => 1.6,
                'category_id' => $categoryAnimal->id,
                'species' => 'Cavia porcellus',
                'price' => 55.00,
                'stock' => 18,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Edad' => '4-5 meses',
                    'Peso' => '1.5-1.8 kg',
                    'Género' => 'Mixto',
                    'Vacunado' => 'Sí',
                    'Certificado' => 'Disponible'
                ],
                'active' => true,
                'user_id' => $seller->id
            ],
            [
                'name' => 'Cuy Inti - Selección Especial',
                'description' => 'Cuy de selección especial Inti, reconocido por su excelente calidad de carne y rusticidad. Adaptado a climas de altura. Peso promedio: 1.4-1.6 kg. Alimentación balanceada con suplementos vitamínicos.',
                'breed' => 'Inti',
                'average_weight' => 1.5,
                'category_id' => $categoryAnimal->id,
                'species' => 'Cavia porcellus',
                'price' => 50.00,
                'stock' => 30,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Edad' => '3-4 meses',
                    'Peso' => '1.4-1.6 kg',
                    'Género' => 'Mixto',
                    'Vacunado' => 'Sí',
                    'Certificado' => 'Disponible'
                ],
                'active' => true,
                'user_id' => $seller->id
            ],
            [
                'name' => 'Alfalfa Premium - 50kg',
                'description' => 'Alfalfa de primera calidad, cosechada en su punto óptimo de madurez. Alto contenido proteico (18-22%). Ideal para alimentación de cuyes en todas las etapas de crecimiento. Presentación en sacos de 50kg.',
                'breed' => 'N/A',
                'average_weight' => 50,
                'category_id' => $categoryAlfalfa->id,
                'species' => 'Medicago sativa',
                'price' => 120.00,
                'stock' => 45,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Presentación' => 'Saco 50kg',
                    'Proteína' => '18-22%',
                    'Fibra' => '25-30%',
                    'Humedad' => '<12%',
                    'Origen' => 'Huánuco'
                ],
                'active' => true,
                'user_id' => $seller->id
            ],
            [
                'name' => 'Alfalfa Deshidratada - 25kg',
                'description' => 'Alfalfa deshidratada de alta calidad, conservando todos sus nutrientes. Fácil almacenamiento y mayor durabilidad. Perfecta para criaderos medianos. Presentación en sacos de 25kg.',
                'breed' => 'N/A',
                'average_weight' => 25,
                'category_id' => $categoryAlfalfa->id,
                'species' => 'Medicago sativa',
                'price' => 75.00,
                'stock' => 60,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Presentación' => 'Saco 25kg',
                    'Proteína' => '16-20%',
                    'Fibra' => '28-32%',
                    'Humedad' => '<10%',
                    'Origen' => 'Huánuco'
                ],
                'active' => true,
                'user_id' => $seller->id
            ],
            [
                'name' => 'Jaula Metálica Premium - Grande',
                'description' => 'Jaula metálica de alta resistencia, ideal para criaderos profesionales. Dimensiones: 120x60x50 cm. Incluye comederos y bebederos. Diseño ventilado para máximo confort de los animales.',
                'breed' => 'N/A',
                'average_weight' => 15,
                'category_id' => $categoryAccesorios->id,
                'species' => 'Accesorio',
                'price' => 180.00,
                'stock' => 12,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Material' => 'Metal galvanizado',
                    'Dimensiones' => '120x60x50 cm',
                    'Capacidad' => '8-10 cuyes',
                    'Incluye' => 'Comederos y bebederos',
                    'Garantía' => '1 año'
                ],
                'active' => true,
                'user_id' => $seller->id
            ],
            [
                'name' => 'Kit de Reproducción Profesional',
                'description' => 'Kit completo para iniciar tu criadero de cuyes. Incluye: 5 cuyes hembras, 1 cuy macho, 50kg de alfalfa, jaula metálica y manual de crianza. Todo lo necesario para empezar con éxito.',
                'breed' => 'N/A',
                'average_weight' => 70,
                'category_id' => $categoryAccesorios->id,
                'species' => 'Kit',
                'price' => 450.00,
                'stock' => 5,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Incluye' => '5 hembras + 1 macho',
                    'Alimento' => '50kg alfalfa',
                    'Jaula' => 'Metálica grande',
                    'Manual' => 'Guía de crianza',
                    'Soporte' => 'Asesoría incluida'
                ],
                'active' => true,
                'user_id' => $seller->id
            ],
            [
                'name' => 'Cuy Andino - Línea Tradicional',
                'description' => 'Cuy de línea tradicional andina, criado siguiendo métodos ancestrales. Adaptado a climas de altura. Excelente rusticidad y resistencia. Peso promedio: 1.0-1.3 kg. Ideal para consumo familiar.',
                'breed' => 'Andino',
                'average_weight' => 1.15,
                'category_id' => $categoryAnimal->id,
                'species' => 'Cavia porcellus',
                'price' => 40.00,
                'stock' => 35,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Edad' => '3-4 meses',
                    'Peso' => '1.0-1.3 kg',
                    'Género' => 'Mixto',
                    'Vacunado' => 'Sí',
                    'Certificado' => 'Disponible'
                ],
                'active' => true,
                'user_id' => $seller->id
            ],
            [
                'name' => 'Balanceado Premium Cuyes - 40kg',
                'description' => 'Alimento balanceado especial para cuyes, formulado con vitaminas y minerales esenciales. Promueve crecimiento rápido y saludable. Presentación en sacos de 40kg. Recomendado para engorde.',
                'breed' => 'N/A',
                'average_weight' => 40,
                'category_id' => $categoryAlfalfa->id,
                'species' => 'Alimento balanceado',
                'price' => 95.00,
                'stock' => 50,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Presentación' => 'Saco 40kg',
                    'Proteína' => '16-18%',
                    'Vitaminas' => 'A, D3, E',
                    'Minerales' => 'Calcio, Fósforo',
                    'Uso' => 'Engorde'
                ],
                'active' => true,
                'user_id' => $seller->id
            ],
            [
                'name' => 'Cuy Reproductor - Línea Selecta',
                'description' => 'Cuy reproductor de línea selecta, con certificado genético. Ideal para mejorar tu plantel de reproducción. Peso promedio: 1.6-2.0 kg. Alto índice de fertilidad. Certificado sanitario completo.',
                'breed' => 'Selecta',
                'average_weight' => 1.8,
                'category_id' => $categoryAnimal->id,
                'species' => 'Cavia porcellus',
                'price' => 85.00,
                'stock' => 8,
                'product_state' => 'Disponible',
                'specifications' => [
                    'Edad' => '5-6 meses',
                    'Peso' => '1.6-2.0 kg',
                    'Género' => 'Macho',
                    'Vacunado' => 'Sí',
                    'Certificado' => 'Genético + Sanitario'
                ],
                'active' => true,
                'user_id' => $seller->id
            ]
        ];

        foreach ($products as $product) {
            GuineaPig::create($product);
        }

        $this->command->info('Productos profesionales creados exitosamente.');
    }
}
