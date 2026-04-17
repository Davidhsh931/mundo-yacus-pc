<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuineaPig;
use App\Models\Cart as CartModel;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use Illuminate\Support\Facades\Http;

class CartController extends Controller
{
    // En CartController.php
public function add($id) {
    $pig = GuineaPig::with('images')->findOrFail($id);

    // Validar stock disponible
    if($pig->stock <= 0) {
        return back()->with('error', '❌ Este producto está agotado. No hay stock disponible.');
    }

    // Si el usuario está autenticado, usar base de datos
    if (auth()->check()) {
        $existingCartItem = CartModel::where('user_id', auth()->id())
            ->where('guinea_pig_id', $id)
            ->first();

        if ($existingCartItem) {
            $currentQty = $existingCartItem->quantity;
            if($currentQty >= $pig->stock) {
                return back()->with('error', "❌ Solo hay {$pig->stock} unidades disponibles. Ya tienes {$currentQty} en tu carrito.");
            }
            $existingCartItem->increment('quantity');
        } else {
            CartModel::create([
                'user_id' => auth()->id(),
                'guinea_pig_id' => $id,
                'quantity' => 1,
            ]);
        }
    } else {
        // Si no está autenticado, usar sesión
        $cart = session()->get('cart', []);
        $currentQty = $cart[$id]['quantity'] ?? 0;
        if($currentQty >= $pig->stock) {
            return back()->with('error', "❌ Solo hay {$pig->stock} unidades disponibles. Ya tienes {$currentQty} en tu carrito.");
        }

        $cart[$id] = [
            "name" => $pig->name,
            "quantity" => $currentQty + 1,
            "price" => $pig->price,
            "image" => $pig->images->first() ? $pig->images->first()->image_path : null
        ];

        session()->put('cart', $cart);
    }

    return back()->with('success', "✅ {$pig->name} agregado al carrito.");
}

    public function remove($id)
    {
        if (auth()->check()) {
            CartModel::where('user_id', auth()->id())
                ->where('guinea_pig_id', $id)
                ->delete();
        } else {
            $cart = session()->get('cart', []);
            if(isset($cart[$id])) {
                unset($cart[$id]);
            }
            session()->put('cart', $cart);
        }
        return redirect()->back();
    }

    public function view()
    {
        // Cargar carrito desde base de datos si está autenticado, sino desde sesión
        if (auth()->check()) {
            $cartItems = CartModel::where('user_id', auth()->id())
                ->with('guineaPig.images')
                ->get();

            $cart = [];
            foreach ($cartItems as $item) {
                $cart[$item->guinea_pig_id] = [
                    'name' => $item->guineaPig->name,
                    'quantity' => $item->quantity,
                    'price' => $item->guineaPig->price,
                    'image' => $item->guineaPig->images->first() ? $item->guineaPig->images->first()->image_path : null
                ];
            }
        } else {
            $cart = session()->get('cart', []);
        }

        // Obtener productos sugeridos (aleatorios, con stock, excluyendo los del carrito)
        $cartIds = array_keys($cart);

        $suggestedProducts = \App\Models\GuineaPig::where('active', true)
            ->where('stock', '>', 0)
            ->whereNotIn('id', $cartIds)
            ->with('images')
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return Inertia::render('Cart', [
            'cart' => $cart,
            'suggestedProducts' => $suggestedProducts,
            'events' => \App\Models\Event::active()->orderBy('event_date', 'asc')->get()
        ]);
    }

    // Función para actualizar cantidades
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99'
        ]);

        if (auth()->check()) {
            // Validar stock disponible
            $pig = GuineaPig::findOrFail($id);
            if($pig->stock < $request->quantity) {
                return back()->with('error', 'Stock insuficiente. Solo hay ' . $pig->stock . ' unidades disponibles.');
            }

            CartModel::where('user_id', auth()->id())
                ->where('guinea_pig_id', $id)
                ->update(['quantity' => $request->quantity]);
        } else {
            $cart = session()->get('cart', []);

            if(isset($cart[$id])) {
                // Validar stock disponible
                $pig = GuineaPig::findOrFail($id);
                if($pig->stock < $request->quantity) {
                    return back()->with('error', 'Stock insuficiente. Solo hay ' . $pig->stock . ' unidades disponibles.');
                }

                $cart[$id]['quantity'] = $request->quantity;
                session()->put('cart', $cart);
            }
        }

        return back()->with('success', 'Cantidad actualizada');
    }

    // --- NUEVO: Función para ver la página de Checkout (Paso 1) ---
    public function viewCheckout()
    {
        // Cargar carrito desde base de datos si está autenticado, sino desde sesión
        if (auth()->check()) {
            $cartItems = CartModel::where('user_id', auth()->id())
                ->with('guineaPig.images')
                ->get();

            $cart = [];
            foreach ($cartItems as $item) {
                $cart[$item->guinea_pig_id] = [
                    'name' => $item->guineaPig->name,
                    'quantity' => $item->quantity,
                    'price' => $item->guineaPig->price,
                    'image' => $item->guineaPig->images->first() ? $item->guineaPig->images->first()->image_path : null
                ];
            }
        } else {
            $cart = session()->get('cart', []);
        }

        if(empty($cart)) {
            return redirect('/cart')->with('error', 'Tu carrito está vacío');
        }

        $total = 0;
        foreach($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return Inertia::render('Checkout', [
            'cart' => $cart,
            'total' => $total
        ]);
    }

    // --- MODIFICADO: Ahora recibe Request para datos reales (Paso 1 cont.) ---
    public function checkout(Request $request)
    {
        // Cargar carrito desde base de datos si está autenticado, sino desde sesión
        if (auth()->check()) {
            $cartItems = CartModel::where('user_id', auth()->id())
                ->with('guineaPig.images')
                ->get();

            $cart = [];
            foreach ($cartItems as $item) {
                $cart[$item->guinea_pig_id] = [
                    'name' => $item->guineaPig->name,
                    'quantity' => $item->quantity,
                    'price' => $item->guineaPig->price,
                    'image' => $item->guineaPig->images->first() ? $item->guineaPig->images->first()->image_path : null
                ];
            }
        } else {
            $cart = session()->get('cart', []);
        }

        if(empty($cart)){
            return redirect()->back();
        }

        // Validamos que vengan la dirección y el método de pago
        $request->validate([
            'shipping_address' => 'required|string|max:255',
            'payment_method' => 'required|in:yape,plin,transfer,cash',
        ]);

        DB::beginTransaction();

        try {
            $total = 0;
            foreach($cart as $item){
                $total += $item['price'] * $item['quantity'];
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'total' => $total,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address, // Dato del form
                'payment_method' => $request->payment_method,     // Dato del form
            ]);

            foreach($cart as $id => $item){
                // Validar stock ANTES de procesar
                $pig = GuineaPig::findOrFail($id);
                if($pig->stock < $item['quantity']){
                    throw new \Exception("Stock insuficiente para '{$pig->name}'. Solo hay {$pig->stock} unidades disponibles.");
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'guinea_pig_id' => $id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price']
                ]);

                $pig = GuineaPig::find($id);
                $pig->stock -= $item['quantity'];

                if($pig->stock < 0){
                    throw new \Exception("Stock insuficiente para el cuy: " . $pig->name);
                }

                $pig->save();
            }

            DB::commit();

            // Eliminar carrito de base de datos si está autenticado
            if (auth()->check()) {
                CartModel::where('user_id', auth()->id())->delete();
            } else {
                session()->forget('cart');
            }

            // --- PASO 3: Redirigir a la página de éxito con el botón de WhatsApp ---
            return redirect()->route('order.success', ['id' => $order->id]);

        } catch(\Exception $e){
            DB::rollBack();
            dd($e->getMessage());
        }
    }

    public function orders()
    {
        $orders = Order::with('items.guineaPig.images')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders', [
            'orders' => $orders,
            'success' => session('success')
        ]);
    }

public function processPayment(Request $request) {
    MercadoPagoConfig::setAccessToken(config('services.mercadopago.token'));
    
    $client = new PreferenceClient();
    $items = [];
    foreach(session('cart') as $product) {
        $items[] = [
            "title" => $product['name'],
            "quantity" => $product['quantity'],
            "unit_price" => (float)$product['price'],
            "currency_id" => "PEN"
        ];
    }

    $preference = $client->create([
        "items" => $items,
        "back_urls" => [
            "success" => route('payment.success'),
            "failure" => route('payment.failure'),
        ],
        "auto_return" => "approved",
    ]);

    // Crear entrada de tracking
        \App\Models\OrderTracking::create([
            'order_id' => $order->id,
            'status' => 'pending',
            'notes' => 'Nuevo pedido recibido',
            'updated_by' => auth()->id()
        ]);

        return inertia('Checkout', ['preferenceId' => $preference->id]);
}

public function iniciarPago(Request $request)
{
    $cart = session()->get('cart', []);
    if (empty($cart)) return back();

    $items = [];
    foreach ($cart as $item) {
        $items[] = [
            "title"       => $item['name'],
            "quantity"    => (int)$item['quantity'],
            "unit_price"  => (float)$item['price'],
            "currency_id" => "PEN", // Soles Peruanos
        ];
    }

    // Llamada directa a la API de Mercado Pago
    $response = Http::withToken('TU_ACCESS_TOKEN_AQUÍ')
        ->post('https://api.mercadopago.com/checkout/preferences', [
            "items" => $items,
            "back_urls" => [
                "success" => route('orders'),
                "failure" => route('cart.view'),
                "pending" => route('orders'),
            ],
            "auto_return" => "approved",
            "payment_methods" => [
                "excluded_payment_types" => [
                    ["id" => "ticket"] // Opcional: quita pago en efectivo si solo quieres tarjetas/yape
                ],
                "installments" => 12 // Máximo de cuotas en Perú
            ]
        ]);

    $preference = $response->json();

    // Redirigimos al init_point de Mercado Pago (la pasarela)
    return Inertia::location($preference['init_point']);
}
}