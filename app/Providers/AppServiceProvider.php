<?php

namespace App\Providers;

use App\Mail\Transport\BrevoApiTransport;
use Illuminate\Support\Facades\Mail;
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
        // Register the Brevo HTTP-API mail transport (SMTP is blocked on Render).
        Mail::extend('brevo', function () {
            return new BrevoApiTransport((string) config('services.brevo.key'));
        });
    }
}
