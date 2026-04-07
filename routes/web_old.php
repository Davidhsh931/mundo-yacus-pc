<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuineaPigController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\VisionController;
use App\Http\Controllers\VisionComentController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\Admin\GuineaPigAdminController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\GuineaPig;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/*
|--------------------------------------------------------------------------
| MUNDO YACUS AI - RUTAS DEL SISTEMA UNIFICADO 2026
|--------------------------------------------------------------------------
*/

// --- 1. INTELIGENCIA ARTIFICIAL (IA) ---
Route::post('/vision/analyze', [VisionController::class, 'analyze']);
Route::post('/vision/analyze/coment', [VisionComentController::class, 'analyze']);
Route::get('/vision/analyze/coment', function () {
    return redirect('/admin/CreatePigComent'); 
});

// Predicción de Stock con Scikit-learn
Route::get('/api/cuy/sugerir-stock/{id}', [GuineaPigController::class, 'sugerirStock']);

// --- 2. DASHBOARD UNIFICADO ---
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    // Productos con stock bajo (menos de 5 unidades)
    $lowStockProducts = GuineaPig::where('stock', '<', 5)
        ->where('active', true)
        ->with(['images'])
        ->get();
    
    // Últimos productos publicados (últimos 10)
    $latestProducts = GuineaPig::where('active', true)
        ->with(['images', 'seller'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    return Inertia::render('Admin/Dashboard', [
        // Datos básicos
        'totalPigs'    => (int) GuineaPig::count(),
        'totalOrders'  => (int) Order::count(),
        'totalClients' => (int) User::where('id', '!=', $user->id)->count(),
        'sales'        => (float) Order::sum('total'),
        
        // Alerta de stock bajo
        'lowStockProducts' => $lowStockProducts,
        'lowStockCount' => $lowStockProducts->count(),
        
        // Últimos productos publicados
        'latestProducts' => $latestProducts,
        
        // ESTO ES LO QUE LE FALTA A TU VUE PARA NO ROMPERSE:
        'analytics'    => [
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr'],
            'values' => [0, 0, 0, 0]
        ],
        // Estructura para el objeto 'stats' que inicializas en el script setup
        'stats' => [
            'stock_sugerido' => 'Calculando...',
            'chart_data' => [
                'values' => [0, 0, 0, 0],
                'labels' => ['Día 1', 'Día 2', 'Día 3', 'Día 4']
            ]
        ]
    ]);
})->middleware(['auth'])->name('dashboard');

Route::get('/admin/dashboard', function () {
    return redirect('/dashboard');
});

// RUTA DE PRUEBA para depuración
Route::get('/test-controller', function() {
    \Log::info('=== RUTA DE PRUEBA EJECUTADA ===');
    file_put_contents(storage_path('logs/test-controller.log'), "Test route: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    return response()->json(['message' => 'Test controller working', 'time' => now()]);
});

// RUTA DE PRUEBA SIN AUTH para ver si el problema es el middleware
Route::post('/test-guinea-pigs', function(Request $request) {
    \Log::info('=== RUTA SIN AUTH EJECUTADA ===');
    file_put_contents(storage_path('logs/test-no-auth.log'), "No auth route: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    return response()->json(['message' => 'No auth working', 'data' => $request->all()]);
});

// RUTA DE PRUEBA para verificar producto 16
Route::get('/test-product-16', function() {
    $pig = \App\Models\GuineaPig::with(['images', 'seller'])->find(16);
    return response()->json([
        'pig' => $pig,
        'specifications_raw' => $pig->specifications,
        'images_count' => $pig->images->count(),
        'seller_name' => $pig->seller ? $pig->seller->name : 'No seller'
    ]);
});

// --- 3. MERCADO DIRECTO (GESTIÓN) ---
Route::prefix('admin')->middleware('auth')->group(function(){
    Route::get('/guinea-pigs', [GuineaPigAdminController::class, 'index'])->name('guinea-pigs.index');
    Route::get('/guinea-pigs/create', [GuineaPigAdminController::class, 'create'])->name('guinea-pigs.create');
    
    // RUTA VITAL: Procesa imagen + stock + especificaciones
    Route::post('/guinea-pigs', [GuineaPigAdminController::class, 'store'])->name('guinea-pigs.store');
    
    // Rutas de edición y actualización
    Route::get('/guinea-pigs/{id}/edit', [GuineaPigAdminController::class, 'edit']);
    Route::put('/guinea-pigs/{id}', [GuineaPigAdminController::class, 'update']);
    Route::delete('/guinea-pigs/{id}', [GuineaPigAdminController::class, 'destroy']);
    
    // --- RUTAS DE PEDIDOS CON SEGREGACIÓN DE RESPONSABILIDADES ---
    
    // Contexto de Cliente (Scope de Usuario): Mis Pedidos
    Route::get('/orders', [\App\Http\Controllers\CustomerOrderController::class, 'index'])->middleware('auth')->name('customer.orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\CustomerOrderController::class, 'show'])->middleware('auth')->name('customer.orders.show');
    Route::patch('/orders/{order}/cancel', [\App\Http\Controllers\CustomerOrderController::class, 'cancel'])->middleware('auth')->name('customer.orders.cancel');
    
    // Contexto de Administrador (Scope Global): Gestión de Ventas
    Route::get('/admin/orders', [\App\Http\Controllers\Admin\OrderAdminController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/{order}', [\App\Http\Controllers\Admin\OrderAdminController::class, 'show'])->name('admin.orders.show');
    Route::patch('/admin/orders/{order}/status', [\App\Http\Controllers\Admin\OrderAdminController::class, 'updateStatus'])->name('admin.orders.update');

    Route::get('/vision', [VisionController::class, 'index'])->name('admin.vision');

    Route::get('/analytics', function () {
    return Inertia::render('Admin/Analytics');
})->name('admin.analytics');
    });

// --- 4. TIENDA Y EXPERIENCIA DE USUARIO ---
Route::get('/', [GuineaPigController::class, 'index'])->name('home');

// Detalle del producto (Polimórfico según rol)
Route::get('/product/{id}', function($id){
    $pig = GuineaPig::with(['images', 'seller'])->findOrFail($id);
    
    // Verificar si el usuario es administrador
    $isAdmin = false;
    if (auth()->check()) {
        $user = auth()->user();
        $isAdmin = ($user->email === 'admin@admin.com' || $user->id === 1);
    }
    
    return Inertia::render('Product', [
        'pig' => $pig,
        'isAdmin' => $isAdmin
    ]);
})->name('products.show');

// Carrito y Checkout
Route::patch('/cart/update/{id}', [CartController::class, 'update']);
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::get('/order-success/{id}', function($id) {
    $order = Order::findOrFail($id);
    return Inertia::render('OrderSuccess', ['order' => $order]);
})->name('order.success');

// --- RUTAS DEL PERFIL ---
Route::get('/profile', function () {
    return Inertia::render('Profile/Edit');
})->middleware('auth')->name('profile');

Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->middleware('auth')->name('profile.update');

Route::put('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->middleware('auth')->name('password.update');

Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->middleware('auth')->name('profile.destroy');

Route::post('/email/verification-notification', [\App\Http\Controllers\ProfileController::class, 'emailVerificationNotification'])->middleware('auth')->name('verification.send');

// --- 5. AUTENTICACIÓN ---
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard'); 
    }
    return back()->withErrors(['email' => 'Credenciales incorrectas.']);
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// --- 6. MANTENIMIENTO TÉCNICO ---
Route::get('/fix-db', function () {
    Schema::table('guinea_pigs', function (Blueprint $table) {
        if (!Schema::hasColumn('guinea_pigs', 'user_id')) {
            $table->foreignId('user_id')->nullable()->constrained('users');
        }
        if (!Schema::hasColumn('guinea_pigs', 'species')) {
            $table->string('species')->nullable();
        }
        // NUEVO: Asegura que el stock exista en la base de datos
        if (!Schema::hasColumn('guinea_pigs', 'stock')) {
            $table->integer('stock')->default(1);
        }
        if (!Schema::hasColumn('guinea_pigs', 'product_state')) {
            $table->string('product_state')->nullable();
        }
        if (!Schema::hasColumn('guinea_pigs', 'specifications')) {
            $table->json('specifications')->nullable();
        }
        if (!Schema::hasColumn('guinea_pigs', 'ia_verification')) {
            $table->json('ia_verification')->nullable();
        }
    });
    return "🚀 Mundo Yacus: Base de datos sincronizada con Stock y Usuario.";
});