<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $currentShop = \Illuminate\Support\Facades\Auth::user()->shop ?? \App\Models\Shop::first();
            $currencySymbol = $currentShop->currency_symbol ?? '₹';
            $view->with('currentShop', $currentShop)
                 ->with('currencySymbol', $currencySymbol);
        });

        \Illuminate\Support\Facades\Blade::directive('price', function ($expression) {
            return "<?php 
                \$__shop = \Illuminate\Support\Facades\Auth::user()->shop ?? \App\Models\Shop::first();
                echo \$__shop ? \$__shop->formatPrice({$expression}) : ('₹' . number_format((float)({$expression}), 2));
            ?>";
        });
    }
}
