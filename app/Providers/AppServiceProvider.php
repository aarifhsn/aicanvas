<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AI\AIProviderManager;
use App\Services\AI\Contracts\AIProviderInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AIProviderManager::class);

        $this->app->bind(AIProviderInterface::class, function ($app) {
            return $app->make(AIProviderManager::class)->make(config('services.ai.default'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
