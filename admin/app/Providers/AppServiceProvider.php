<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        // "Süper Admin" rolündeki kullanıcılar, tanımlı izinlere bakılmaksızın
        // panelin tüm alanlarına erişebilir.
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('Süper Admin') ? true : null;
        });
    }
}
