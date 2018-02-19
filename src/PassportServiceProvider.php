<?php

namespace Dusterio\LumenPassport;

use Dusterio\LumenPassport\Console\Commands\Purge;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;

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
        $this->app->singleton(Connection::class, function() {
            return $this->app['db.connection'];
        });

        if (preg_match('/5\.6\.\d+/', $this->app->version())) {
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
    }
}
