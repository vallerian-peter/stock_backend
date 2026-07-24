<?php

namespace App\Providers;

use App\Contracts\SupportRequestExporter;
use App\Services\GoogleSheetsSupportExporter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            SupportRequestExporter::class,
            GoogleSheetsSupportExporter::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
