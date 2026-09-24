<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\Cart;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(
                        ['layouts.app', 'home', 'contact', 'catalog.*', 'cart.*', 'checkout.*', 'orders.*', 'errors.*'],
            function ($view) {
                $view->with('site', Setting::site());
            }
        );

        View::composer('layouts.app', function ($view) {
            $view->with('cartCount', app(Cart::class)->count());
        });
    }
}