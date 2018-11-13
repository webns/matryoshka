<?php

namespace Laracasts\Matryoshka;

use Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;

class MatryoshkaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param Kernel $kernel
     */
    public function boot(Kernel $kernel)
    {
        if ($this->app->isLocal()) {
            $kernel->pushMiddleware('Laracasts\Matryoshka\FlushViews');
        }

        $this->publishes([
            __DIR__ . '/../config/matryoshka.php' => config_path('matryoshka.php'),
        ], 'config');

        Blade::directive('cache', function ($expression) {
            $version = explode('.', $this->app::VERSION);
            // Starting with laravel 5.3 the parens are not included in the expression string.
            if ($version[1] > 2) {
                return "<?php if (! app('Laracasts\Matryoshka\BladeDirective')->setUp({$expression})) : ?>";
            }
            return "<?php if (! app('Laracasts\Matryoshka\BladeDirective')->setUp{$expression}) : ?>";
        });

        Blade::directive('endcache', function () {
            return "<?php endif; echo app('Laracasts\Matryoshka\BladeDirective')->tearDown() ?>";
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/matryoshka.php', 'matryoshka'
        );

        $this->app->singleton(BladeDirective::class, function ($app) {
            return new BladeDirective(
                $app->config->get('matryoshka'),
                $app['cache.store']
            );
        });
    }
}
