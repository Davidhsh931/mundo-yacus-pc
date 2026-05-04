<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\GuineaPig;
use App\Models\GuineaPigImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Usuario Admin
        User::create([
            'name' => 'David',
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'is_approved' => true,
        ]);

        // 2. Crear Usuario Cliente
        User::create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@cliente.com',
            'password' => Hash::make('12345678'),
            'role' => 'cliente',
            'is_approved' => true,
        ]);

        // 3. Crear Categorías profesionales
        $categories = [
            ['name' => 'Animales'],
            ['name' => 'Alfalfas'],
            ['name' => 'Accesorios'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate($category);
        }

        // 4. Crear productos profesionales usando el seeder
        $this->call(GuineaPigSeeder::class);

        // 5. Crear eventos de ejemplo
        $this->call(EventSeeder::class);

        // 6. Crear configuración del sistema
        $this->call(SettingsSeeder::class);

        $this->command->info('✅ Base de datos poblada con datos profesionales.');
    }
}