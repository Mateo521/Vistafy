<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Services\ImageProcessingService;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Registrar Policies
        Gate::policy(Photo::class, PhotoPolicy::class);
    }
}
