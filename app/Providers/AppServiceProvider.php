<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CartService;
use Illuminate\Support\Facades\View;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // CartService as singleton (same instance per request)
        $this->app->singleton(CartService::class, function () {
            return new CartService();
        });
    }

    public function boot(): void
    {
        // Share cart count with all views
        View::composer('*', function ($view) {
            try {
                $cartService = app(CartService::class);
                $view->with('_cartCount', $cartService->count());
            } catch (\Exception $e) {
                $view->with('_cartCount', 0);
            }
        });
    }
}
