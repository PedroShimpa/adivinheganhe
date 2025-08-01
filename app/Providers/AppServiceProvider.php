<?php

namespace App\Providers;

use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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

        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });







        # habilitar a dubbhar em ambiente de produção para os adms
        if (app()->environment('local')) {
            return; 
        }

        if (Auth::check() && Auth::user()->isAdmin()) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }
    }
}
