<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RestoreUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunos productos de ejemplo
        \App\Models\GuineaPig::create([
            'name' => 'Cuy Premium',
            'breed' => 'Mejorada',
            'description' => 'Cuy de alta calidad criado en los valles de Yacus',
            'price' => 89.99,
            'stock' => 15,
            'category_id' => 1,
            'average_weight' => 1.2,
            'specifications' => json_encode([
                ['key' => 'Peso', 'value' => '1.2 kg'],
                ['key' => 'Edad', 'value' => '3 meses'],
                ['key' => 'Raza', 'value' => 'Mejorada']
            ])
        ]);

        // Crear pedidos de prueba
        $order1 = \App\Models\Order::create([
            'user_id' => 2, // Cliente
            'total' => 179.98,
            'status' => 'pending',
            'shipping_address' => 'Dirección de prueba 123',
            'payment_method' => 'yape'
        ]);

        $order2 = \App\Models\Order::create([
            'user_id' => 2, // Cliente
            'total' => 89.99,
            'status' => 'paid',
            'shipping_address' => 'Dirección de prueba 456',
            'payment_method' => 'plin'
        ]);

        // Crear items de los pedidos
        \App\Models\OrderItem::create([
            'order_id' => $order1->id,
            'guinea_pig_id' => 1,
            'quantity' => 2,
            'unit_price' => 89.99
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order2->id,
            'guinea_pig_id' => 1,
            'quantity' => 1,
            'unit_price' => 89.99
        ]);

        // Crear notificaciones de ejemplo para el admin (user_id = 1)
        \App\Models\Notification::create([
            'user_id' => 1,
            'message' => "📦 Nuevo pedido #{$order1->id} recibido",
            'type' => 'order',
            'read' => false,
            'data' => json_encode(['order_id' => $order1->id])
        ]);

        \App\Models\Notification::create([
            'user_id' => 1,
            'message' => "📦 Nuevo pedido #{$order2->id} recibido",
            'type' => 'order',
            'read' => false,
            'data' => json_encode(['order_id' => $order2->id])
        ]);

        \App\Models\Notification::create([
            'user_id' => 1,
            'message' => '⚠️ Stock bajo en: Cuy Premium',
            'type' => 'warning',
            'read' => false,
            'data' => json_encode(['product_id' => 1])
        ]);

        \App\Models\Notification::create([
            'user_id' => 1,
            'message' => '💬 Nuevo comentario en producto',
            'type' => 'comment',
            'read' => false,
            'data' => json_encode(['product_id' => 1, 'comment_id' => 15])
        ]);

        \App\Models\Notification::create([
            'user_id' => 1,
            'message' => '🧪 PRUEBA: Clic navegar a producto',
            'type' => 'test',
            'read' => false,
            'data' => json_encode(['product_id' => 1])
        ]);

        \App\Models\Notification::create([
            'user_id' => 1,
            'message' => 'ℹ️ Nueva actualización del sistema',
            'type' => 'info',
            'read' => false,
            'data' => json_encode([])
        ]);
    }
}
