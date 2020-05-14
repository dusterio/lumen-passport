<?php

namespace Dusterio\LumenPassport;

use Dusterio\LumenPassport\Console\Commands\Purge;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;
use Laravel\Passport\Passport;

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
        if ($this->app['config'] instanceof Repository && method_exists(Passport::class, 'setClientUuids')) {
            Passport::setClientUuids($this->app['config']->get('passport.client_uuids', false));
        }
    }
}
