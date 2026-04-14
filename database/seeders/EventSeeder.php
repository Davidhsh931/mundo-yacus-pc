<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Eventos de ejemplo para Mundo Yacus
        $events = [
            [
                'title' => 'Lanzamiento de Nueva Línea de Cuyes Premium',
                'description' => 'Presentamos nuestra nueva línea de cuyes de raza mejorada con certificación sanitaria. Perfectos para reproducción y consumo familiar.',
                'event_date' => now()->addDays(15),
                'is_active' => true,
            ],
            [
                'title' => 'Capacitación Gratuita: Manejo de Alimentación',
                'description' => 'Aprende las mejores prácticas para alimentar tus cuyes con forraje local. Incluye módulo sobre preparación de balanced casero.',
                'event_date' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'title' => 'Feria de Cuyes - Edición Especial',
                'description' => 'Encuentro de productores de toda la región. Ventas de reproductores, intercambio de experiencias y premiación a los mejores ejemplares.',
                'event_date' => now()->addDays(45),
                'is_active' => true,
            ],
        ];

        foreach ($events as $event) {
            Event::updateOrCreate(
                ['title' => $event['title']],
                $event
            );
        }
    }
}
