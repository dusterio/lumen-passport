<?php

namespace Dusterio\LumenPassport;

use Dusterio\LumenPassport\Console\Commands\Purge;
use Illuminate\Database\Connection;
use Illuminate\Hashing\HashManager;

/**
 * Class CustomQueueServiceProvider
 * @package App\Providers
 */
class PassportServiceProvider extends \Laravel\Passport\PassportServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(
            Connection::class,
            function () {
                return $this->app['db.connection'];
            }
        );

        if (preg_match('/5\.[678]\.\d+/', $this->app->version())) {
            $this->app->singleton(
                HashManager::class,
                function ($app) {
                    return new HashManager($app);
                }
            );
        }

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    Purge::class,
                ]
            );
        }

        parent::boot();
    }

    /**
     * Override
     *
     * @return void
     *
     */
    public function register()
    {
        $this->registerAuthorizationServer();
        $this->registerResourceServer();
        $this->registerGuard();
    }
}
