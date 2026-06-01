<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Force URL scheme to match the current request
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        
        // Force root URL to handle subdirectory hosting with /public
        if (app()->environment('production')) {
            $appUrl = env('APP_URL', request()->getSchemeAndHttpHost());
            // Ensure /public is in the URL if not already
            if (!str_ends_with($appUrl, '/public')) {
                $appUrl = rtrim($appUrl, '/') . '/public';
            }
            URL::forceRootUrl($appUrl);
        }
    }
}
