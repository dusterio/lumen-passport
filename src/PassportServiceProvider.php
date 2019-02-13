<?php

namespace Dusterio\LumenPassport;

use Dusterio\LumenPassport\Console\Commands\Purge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;

/**
 * Class CustomQueueServiceProvider
 * @package App\Providers
 */
class PassportServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Connection::class, function () {
            return DB::connection(env('PASSPORT_CONNECTION', 'default'));
        });

        if (preg_match('/5\.[67]\.\d+/', $this->app->version())) {
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
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
