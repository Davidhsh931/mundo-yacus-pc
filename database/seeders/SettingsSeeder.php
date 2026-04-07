<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuraciones iniciales de Mundo Yacus
        Setting::set('whatsapp_number', '51987654321', 'text', 'Número de WhatsApp para recibir comprobantes de pago');
        Setting::set('business_name', 'Mundo Yacus', 'text', 'Nombre del negocio');
        Setting::set('yape_number', '987654321', 'text', 'Número de Yape para pagos');
        Setting::set('plin_number', '987654321', 'text', 'Número de Plin para pagos');
        Setting::set('currency_symbol', 'S/', 'text', 'Símbolo de moneda');
        Setting::set('enable_whatsapp', '1', 'boolean', 'Habilitar botones de WhatsApp');
        
        $this->command->info('✅ Configuraciones iniciales de Mundo Yacus creadas');
    }
}
