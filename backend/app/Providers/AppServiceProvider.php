<?php

namespace App\Providers;

use App\Domain\Poll\Contracts\ShortCodeGeneratorInterface;
use App\Domain\Poll\Generators\RandomShortCodeGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the short code generator interface to its default implementation.
        // To swap strategies, replace RandomShortCodeGenerator with any other
        // class that implements ShortCodeGeneratorInterface.
        $this->app->bind(
            ShortCodeGeneratorInterface::class,
            RandomShortCodeGenerator::class,
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
