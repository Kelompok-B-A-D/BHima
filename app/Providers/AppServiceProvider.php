<?php

namespace App\Providers;

use App\Contracts\AnalysisProvider;
use App\Services\Analysis\DeepSeekProvider;
use App\Services\Analysis\MockProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AnalysisProvider::class, function ($app) {
            $provider = strtolower(trim((string) config('legal_analysis.provider', 'mock')));

            return $provider === 'deepseek'
                ? $app->make(DeepSeekProvider::class)
                : $app->make(MockProvider::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            $this->app->environment('production') ||
            str_starts_with((string) config('app.url'), 'https://') && request()->header('x-forwarded-proto') === 'https'
        ) {
            URL::forceScheme('https');
        }
    }
}
