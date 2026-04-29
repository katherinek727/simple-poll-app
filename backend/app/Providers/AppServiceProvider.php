<?php

namespace App\Providers;

use App\Domain\Poll\Contracts\ShortCodeGeneratorInterface;
use App\Domain\Poll\Factories\PollFactory;
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

        // PollFactory is resolved via the container so its ShortCodeGeneratorInterface
        // dependency is automatically injected — no manual wiring needed.
        $this->app->bind(PollFactory::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
