<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuineaPigController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\VisionController;
use App\Http\Controllers\VisionComentController;
use App\Http\Controllers\Admin\GuineaPigAdminController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\CommentController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\GuineaPig;
use App\Models\Order;
use App\Models\User;

// FUERA de cualquier Route::group o Route::prefix
Route::match(['post', 'put'], '/admin/guinea-pigs/{id}', [GuineaPigAdminController::class, 'update']);

/*
|--------------------------------------------------------------------------
| MUNDO YACUS AI - RUTAS PROTEGIDAS Y SEGREGADAS 2026
|--------------------------------------------------------------------------
*/

// --- 1. INTELIGENCIA ARTIFICIAL (IA) ---
Route::post('/vision/analyze', [VisionController::class, 'analyze']);
Route::post('/vision/analyze/coment', [VisionComentController::class, 'analyze']);
Route::get('/vision/analyze/coment', function () {
    return redirect('/admin/CreatePigComent'); 
});

// --- 2. DASHBOARD INTELIGENTE (REDISRECCIÓN POR ROL) ---
Route::get('/dashboard', function () {
    $user = auth()->user();

    // SI ES ADMIN: Verificar aprobación
    if ($user->role === 'admin') {
        if (!$user->is_approved) {
            return redirect('/')->with('error', 'Tu cuenta de administrador está pendiente de aprobación por el propietario.');
        }

        // Ve el panel de control con estadísticas
        $lowStockProducts = GuineaPig::where('stock', '<', 5)
            ->where('active', true)
            ->with(['images'])
            ->get();
        
        $latestProducts = GuineaPig::where('active', true)
            ->with(['images', 'seller'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Obtener eventos para el Dashboard
        try {
            $events = \App\Models\Event::active()
                ->upcoming()
                ->orderBy('event_date', 'asc')
                ->take(5)
                ->get();

            // Agregar accesores a cada evento
            $events->each(function ($event) {
                $event->formatted_date = $event->formatted_date;
                $event->image_url = $event->image_url;
            });
        } catch (\Exception $e) {
            \Log::error('Error loading events on dashboard: ' . $e->getMessage());
            $events = collect();
        }
        
        return Inertia::render('Admin/Dashboard', [
            'totalPigs'    => (int) GuineaPig::count(),
            'totalOrders'  => (int) Order::count(),
            'totalClients' => (int) User::where('role', 'cliente')->count(),
            'sales'        => (float) Order::sum('total'),
            'lowStockProducts' => $lowStockProducts,
            'lowStockCount' => $lowStockProducts->count(),
            'latestProducts' => $latestProducts,
            'events' => $events,
            'analytics'    => [
                'labels' => ['Ene', 'Feb', 'Mar', 'Abr'],
                'values' => [0, 0, 0, 0]
            ],
            'stats' => [
                'stock_sugerido' => 'Calculando...',
                'chart_data' => [
                    'values' => [0, 0, 0, 0],
                    'labels' => ['Día 1', 'Día 2', 'Día 3', 'Día 4']
                ]
            ]
        ]);
    }
    
    // SI ES CLIENTE: Lo mandamos directo a sus pedidos (No ve estadísticas)
    return redirect()->route('customer.orders.index');

})->middleware(['auth'])->name('dashboard');

Route::get('/admin/dashboard', function () {
    return redirect('/dashboard');
});

use App\Http\Controllers\Admin\AiTrainingController;
use App\Http\Controllers\Admin\UserAdminController;

// --- 3. MÓDULO ADMINISTRATIVO (BLOQUEO TOTAL PARA CLIENTES) ---
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function(){
    
    // Gestión de Guinea Pigs
    Route::get('/guinea-pigs', [GuineaPigAdminController::class, 'index'])->name('guinea-pigs.index');
    Route::get('/guinea-pigs/create', [GuineaPigAdminController::class, 'create'])->name('guinea-pigs.create');
    Route::post('/guinea-pigs', [GuineaPigAdminController::class, 'store'])->name('guinea-pigs.store');
    Route::get('/guinea-pigs/{id}/edit', [GuineaPigAdminController::class, 'edit'])->name('guinea-pigs.edit');
    Route::delete('/guinea-pigs/{id}', [GuineaPigAdminController::class, 'destroy'])->name('guinea-pigs.destroy');
    
    // Centro de Entrenamiento IA
Route::get('/ai-training', [AiTrainingController::class, 'index'])->name('ai-training.index');
Route::post('/ai-training', [AiTrainingController::class, 'store'])->name('ai-training.store');
Route::post('/ai-training/{category}', [AiTrainingController::class, 'update'])->name('ai-training.update');
Route::delete('/admin/ai-training/{category}', [AiTrainingController::class, 'destroy'])->name('ai-training.destroy');
Route::post('/admin/ai-training/{id}/restore', [AiTrainingController::class, 'restore'])->name('ai-training.restore');
Route::delete('/admin/ai-training/{id}/force', [AiTrainingController::class, 'forceDelete'])->name('ai-training.force-delete');

    // Papelera
    Route::get('/guinea-pigs/trashed', [GuineaPigAdminController::class, 'trashed'])->name('guinea-pigs.trashed');
    Route::post('/guinea-pigs/{id}/restore', [GuineaPigAdminController::class, 'restore'])->name('guinea-pigs.restore');
    Route::delete('/guinea-pigs/{id}/force-delete', [GuineaPigAdminController::class, 'forceDelete'])->name('guinea-pigs.force-delete');
    
    // Gestión de Ventas Global
    Route::get('/orders', [OrderAdminController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrderAdminController::class, 'show'])->name('admin.orders.show');
    Route::patch('/orders/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('admin.orders.update');

    // Gestión de Usuarios
    Route::get('/users', [UserAdminController::class, 'index'])->name('admin.users.index');
    Route::post('/users/{user}/approve', [UserAdminController::class, 'approve'])->name('admin.users.approve');
    Route::post('/users/{user}/reject', [UserAdminController::class, 'reject'])->name('admin.users.reject');
    Route::patch('/users/{user}/role', [UserAdminController::class, 'changeRole'])->name('admin.users.change-role');

    Route::get('/vision', [VisionController::class, 'index'])->name('admin.vision');
    Route::get('/analytics', function () {
        return Inertia::render('Admin/Analytics');
    })->name('admin.analytics');
    
    // Ruta para toggle active
    Route::patch('/guinea-pigs/{id}/toggle-active', function($id) {
        $pig = \App\Models\GuineaPig::findOrFail($id);
        $pig->active = !$pig->active;
        $pig->save();

        return back(); // Regresa a la misma página
    });
});

// --- 4. TIENDA Y EXPERIENCIA PÚBLICA ---
Route::get('/', [GuineaPigController::class, 'index'])->name('home');

// Productos filtrados por categoría
Route::get('/products', [ProductsController::class, 'index'])->name('products.category');

// Detalle del producto (Detección de rol corregida)
Route::get('/product/{id}', function($id){
    $pig = GuineaPig::with(['images', 'seller', 'comments.user', 'category'])->findOrFail($id);
    
    // 🛡️ Lógica de Visibilidad Inteligente:
    // SI el producto está inactivo 
    // Y el usuario NO está logueado O NO es administrador...
    if (!$pig->active && !(auth()->check() && auth()->user()->role === 'admin')) {
        abort(404, 'Este producto ya no está disponible en la tienda.');
    }
    
    $isAdmin = false;
    $canComment = false;
    
    if (auth()->check()) {
        $user = auth()->user();
        $isAdmin = ($user->role === 'admin');
        
        if ($user->role === 'admin') {
            $canComment = true;
        } else {
            // Solo puede comentar si tiene al menos una orden PAGADA, ENVIADA o ENTREGADA
            $canComment = $user->orders()
                ->whereIn('status', ['paid', 'shipped', 'delivered']) 
                ->whereHas('items', fn($q) => $q->where('guinea_pig_id', $id))
                ->exists();
        }
    }
    
    return Inertia::render('Product', [
        'pig' => $pig,
        'isAdmin' => $isAdmin,
        'canComment' => $canComment
    ]);
})->name('products.show');

// Carrito
Route::patch('/cart/update/{id}', [CartController::class, 'update']);
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove/{id}', [CartController::class, 'remove']);
Route::get('/cart', [CartController::class, 'view']);

// Checkout (Solo Clientes Autenticados)
Route::get('/checkout', [CartController::class, 'viewCheckout'])->middleware('auth')->name('checkout');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->middleware('auth')->name('checkout.process');

// --- 5. PANEL DE CLIENTE (MIS PEDIDOS) ---
Route::get('/orders', [CustomerOrderController::class, 'index'])->middleware('auth')->name('customer.orders.index');
Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->middleware('auth')->name('customer.orders.show');
Route::patch('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->middleware('auth')->name('customer.orders.cancel');

Route::get('/order-success/{id}', function($id) {
    $order = Order::findOrFail($id);
    
    // Cargar configuraciones dinámicas y limpiar números
    $whatsappNumber = \App\Models\Setting::get('whatsapp_number', '51987654321');
    $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber); // Solo números
    
    $yapeNumber = \App\Models\Setting::get('yape_number', '987654321');
    $plinNumber = \App\Models\Setting::get('plin_number', '987654321');
    
    $settings = [
        'whatsappNumber' => $whatsappNumber,
        'yapeNumber' => $yapeNumber,
        'plinNumber' => $plinNumber,
        'timestamp' => time(), // Forzar recarga de Vue
    ];
    
    return Inertia::render('OrderSuccess', array_merge(['order' => $order], $settings));
})->name('order.success');

// --- 6. PERFIL Y SEGURIDAD ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', function () { return Inertia::render('Profile/Edit'); })->name('profile');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- 7. AUTENTICACIÓN PERSONALIZADA ---
Route::get('/login', function () { return Inertia::render('Auth/Login'); })->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        // Sincronizar carrito de sesión con base de datos
        $sessionCart = session()->get('cart', []);
        if (!empty($sessionCart) && auth()->check()) {
            foreach ($sessionCart as $guineaPigId => $item) {
                $existingCartItem = \App\Models\Cart::where('user_id', auth()->id())
                    ->where('guinea_pig_id', $guineaPigId)
                    ->first();

                if ($existingCartItem) {
                    // Si ya existe, actualizar cantidad
                    $existingCartItem->update(['quantity' => $item['quantity']]);
                } else {
                    // Si no existe, crear nuevo
                    \App\Models\Cart::create([
                        'user_id' => auth()->id(),
                        'guinea_pig_id' => $guineaPigId,
                        'quantity' => $item['quantity'],
                    ]);
                }
            }
            // Eliminar carrito de sesión después de sincronizar
            session()->forget('cart');
        }

        return redirect()->intended('/dashboard');
    }
    return back()->withErrors(['email' => 'Credenciales incorrectas.']);
})->name('login.store');

Route::get('/register', function () { return Inertia::render('Auth/Register'); })->name('register');

// Rutas separadas para registro de comprador y vendedor
Route::get('/register/comprador', function () {
    return Inertia::render('Auth/RegisterComprador');
})->name('register.comprador');

Route::post('/register/comprador', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
            // Verificar que el nombre de usuario no exista con ningún dominio
            if (User::where('email', 'LIKE', $value . '@%')->exists()) {
                $fail('El nombre de usuario ya está en uso.');
            }
        }],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    // Generar correo automático con dominio @cliente.com
    $email = $validated['username'] . '@cliente.com';

    $user = User::create([
        'name' => $validated['name'],
        'email' => $email,
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        'role' => 'cliente',
    ]);

    Auth::login($user);
    return redirect('/dashboard');
})->name('register.comprador.store');

Route::get('/register/vendedor', function () {
    return Inertia::render('Auth/RegisterVendedor');
})->name('register.vendedor');

Route::post('/register/vendedor', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
            // Verificar que el nombre de usuario no exista con ningún dominio
            if (User::where('email', 'LIKE', $value . '@%')->exists()) {
                $fail('El nombre de usuario ya está en uso.');
            }
        }],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    // Generar correo automático con dominio @admin.com
    $email = $validated['username'] . '@admin.com';

    $user = User::create([
        'name' => $validated['name'],
        'email' => $email,
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        'role' => 'admin',
        'is_approved' => false,
    ]);

    Auth::login($user);
    return redirect('/dashboard');
})->name('register.vendedor.store');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', function ($attribute, $value, $fail) {
            // Verificar que el nombre de usuario no exista con ningún dominio
            $username = explode('@', $value)[0];
            if (User::where('email', 'LIKE', $username . '@%')->exists()) {
                $fail('El nombre de usuario ya está en uso.');
            }
        }],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    // Extraer y limpiar el dominio del email (robusto a mayúsculas/espacios)
    $emailDomain = strtolower(trim(substr(strrchr($validated['email'], "@"), 1)));

    // Asignación de rol automática por dominio (lógica clara y explícita)
    if ($emailDomain === 'admin.com') {
        $role = 'admin';
        $isApproved = false; // Los admins requieren aprobación
    } elseif ($emailDomain === 'cliente.com') {
        $role = 'cliente';
        $isApproved = true; // Los clientes no requieren aprobación
    } else {
        return back()->withErrors(['email' => 'Dominio no autorizado. Solo @admin.com o @cliente.com permitidos.']);
    }

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        'role' => $role,
        'is_approved' => $isApproved,
    ]);

    Auth::login($user);
    return redirect('/dashboard');
})->name('register.store');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// --- RUTAS DE CONFIGURACIONES (SOLO ADMIN) ---
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/admin/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('admin.settings.update');
    Route::get('/api/settings', [App\Http\Controllers\Admin\SettingsController::class, 'getPublicSettings'])->name('api.settings.public');
    
    // --- RUTAS PARA NOTIFICACIONES ---
    Route::get('/admin/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::patch('/admin/notifications/{id}/read', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('admin.notifications.read');
    Route::patch('/admin/notifications/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('admin.notifications.mark-all-read');
    
    // --- RUTAS PARA EVENTOS/NOTICIAS ---
    Route::prefix('admin')->group(function () {
        Route::resource('events', EventController::class)->names('admin.events');
        Route::post('settings/update', [EventController::class, 'updateSettings'])->name('admin.events.settings.update');
    });
});

// --- RUTA DE PRUEBA PARA NOTIFICACIONES (TEMPORAL) ---
Route::get('/api/notifications/test', [App\Http\Controllers\Admin\NotificationController::class, 'test']);

// --- RUTA ESPECIAL PARA EDITAR GUINEA PIGS (Fuera de grupos para evitar conflictos) ---
Route::post('/admin/guinea-pigs/{id}', [GuineaPigAdminController::class, 'update'])->name('guinea-pigs.update.post');

// --- RUTAS DE COMENTARIOS ---
Route::middleware('auth')->group(function () {
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

// --- RUTA TEMPORAL DE RESCATE PARA ADMIN ---
Route::get('/rescue-admin', function () {
    // Pon aquí el correo con el que intentas entrar
    $email = 'tu-correo@admin.com'; 
    
    $user = \App\Models\User::where('email', $email)->first();

    if ($user) {
        $user->role = 'admin'; // Forzamos el rol exacto
        $user->save();
        return "ÉXITO: El usuario {$email} ahora tiene rol 'admin'. Prueba loguear.";
    }

    return "ERROR: No se encontró al usuario con el correo {$email} en la base de datos de Railway.";
});