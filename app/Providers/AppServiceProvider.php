<?php

namespace App\Providers;

use App\Services\MailConfigurationService;
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
        try {
            MailConfigurationService::applyFromDatabase();
        } catch (\Throwable) {
            // La tabla puede no existir aún durante migraciones.
        }
    }
}
