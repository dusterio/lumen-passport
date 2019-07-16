<?php

namespace Dusterio\LumenPassport;

use Dusterio\LumenPassport\Console\Commands\Purge;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Class CustomQueueServiceProvider
 * @package App\Providers
 */
class PassportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $laravel = new \Laravel\Passport\PassportServiceProvider($this->app);
        $laravel->boot();

        $this->app->singleton(Connection::class, function() {
            return $this->app['db.connection'];
        });

        if (preg_match('/5\.[678]\.\d+/', $this->app->version())) {
            $this->app->singleton(\Illuminate\Hashing\HashManager::class, function ($app) {
                return new \Illuminate\Hashing\HashManager($app);
            });
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                Purge::class
            ]);
        }
    }
    /**
     * @return void
     */
    public function register()
    {
        $app = new ProxyApplication($this->app);
        $laravel = new \Laravel\Passport\PassportServiceProvider($app);
        $laravel->register();
    }
}
