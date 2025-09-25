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
        // When serving through Cloudflare Tunnel, the browser hits HTTPS while
        // the origin (php artisan serve) is HTTP. Force HTTPS so generated
        // URLs and cookies are consistent and avoid mixed content / 419 issues.
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        if ($forwardedProto === 'https' || str_starts_with((string) env('APP_URL'), 'https://')) {
            URL::forceScheme('https');
            // Ensure cookies are marked secure only when the request is HTTPS
            config([
                'session.secure' => true,
                'session.same_site' => config('session.same_site', 'lax'),
                'session.domain' => null,
            ]);
        } else {
            // Localhost HTTP: don't require secure cookies
            config(['session.secure' => false]);
        }
    }
}
