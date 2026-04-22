<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Calcular contador del carrito
        $cartCount = 0;
        if ($request->user()) {
            // Usuario autenticado: contar desde base de datos
            $cartCount = \App\Models\Cart::where('user_id', $request->user()->id)->count();
        } else {
            // Usuario no autenticado: contar desde sesión
            $cart = session()->get('cart', []);
            $cartCount = count($cart);
        }

        try {
            $settings = [
                'banner_text' => \App\Models\Setting::get('banner_text', ''),
                'banner_active' => \App\Models\Setting::get('banner_active', '0'),
            ];
        } catch (\Exception $e) {
            \Log::warning('Error loading settings in middleware: ' . $e->getMessage());
            $settings = [
                'banner_text' => '',
                'banner_active' => '0',
            ];
        }

        try {
            $categories = \App\Models\Category::withCount('guineaPigs')->get();
        } catch (\Exception $e) {
            \Log::warning('Error loading categories in middleware: ' . $e->getMessage());
            $categories = collect();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'settings' => $settings,
            'categories' => $categories,
            'cartCount' => $cartCount,
        ];
    }
}
