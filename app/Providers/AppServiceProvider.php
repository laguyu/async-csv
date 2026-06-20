<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\DataProcessorInterface;
use App\Services\CsvProductProcessor;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DataProcessorInterface::class, CsvProductProcessor::class);
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
