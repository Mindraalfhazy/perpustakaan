<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
        Paginator::useBootstrapFive(); // ganti ke useBootstrapFour() kalau project pakai Bootstrap 4

        Facades\View::composer('layouts.menu', function (View $view) {
            $isActive = function (string $routeName): string {
                if ($routeName == 'home') {
                    $isHomePath = request()->path() == '/';
                    return $isHomePath ? "active" : "";
                }

                $routePrefix = route($routeName);
                $currentUrl = request()->url();
                return Str::startsWith($currentUrl, $routePrefix) ? "active" : "";
            };
            $view->with('isActive', $isActive);
        });
    }
}