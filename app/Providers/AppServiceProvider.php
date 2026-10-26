<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        if (
            request()->server('HTTP_X_FORWARDED_PROTO') === 'https' ||
            request()->secure() ||
            str_starts_with(config('app.url'), 'https://') ||
            (request()->getHost() && str_contains(request()->getHost(), 'nexteck.uk'))
        ) {
            URL::forceScheme('https');
        }
    }
}
