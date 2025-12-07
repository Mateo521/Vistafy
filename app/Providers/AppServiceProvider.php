<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Services\ImageProcessingService;
use Illuminate\Support\Facades\URL;
use App\Services\CartService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageProcessingService::class, function ($app) {
            return new ImageProcessingService();
        });

        $this->app->singleton(CartService::class, function ($app) {
            return new CartService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        if (
            $this->app->environment('production') ||
            request()->header('X-Forwarded-Proto') === 'https'
        ) {
            URL::forceScheme('https');
        }

        // También forzar HTTPS si APP_URL es https
        if (str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        // Registrar Policies
        Gate::policy(Photo::class, PhotoPolicy::class);
    }
}
