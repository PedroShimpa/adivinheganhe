<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {

        Paginator::useBootstrapFive();
        if (env('APP_ENV') === 'production') {
            $url->forceScheme('https');
        }

        Route::prefix('api')->group(function () {
            request()->headers->set('Accept', 'application/json');
        });
    }
}
