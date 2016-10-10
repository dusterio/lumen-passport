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

        if ($this->app->runningInConsole()) {
            $this->commands([
                Purge::class
            ]);
        }

        $this->registerRoutes();
    }
    /**
     * @return void
     */
    public function register()
    {
    }

    /**
     * Register routes for transient tokens, clients, and personal access tokens.
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->forAccessTokens();
        $this->forTransientTokens();
        $this->forClients();
        $this->forPersonalAccessTokens();
    }

    /**
     * Register the routes for retrieving and issuing access tokens.
     *
     * @return void
     */
    public function forAccessTokens()
    {
        $this->app->post('/oauth/token', [
            'uses' => '\Dusterio\LumenPassport\Http\Controllers\AccessTokenController@issueToken'
        ]);

        $this->app->group(['middleware' => ['auth']], function () {
            $this->app->get('/oauth/tokens', [
                'uses' => '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@forUser',
            ]);

            $this->app->delete('/oauth/tokens/{token_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@destroy',
            ]);
        });
    }

    /**
     * Register the routes needed for refreshing transient tokens.
     *
     * @return void
     */
    public function forTransientTokens()
    {
        $this->app->post('/oauth/token/refresh', [
            'middleware' => ['auth'],
            'uses' => '\Laravel\Passport\Http\Controllers\TransientTokenController@refresh',
        ]);
    }

    /**
     * Register the routes needed for managing clients.
     *
     * @return void
     */
    public function forClients()
    {
        $this->app->group(['middleware' => ['auth']], function () {
            $this->app->get('/oauth/clients', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@forUser',
            ]);

            $this->app->post('/oauth/clients', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@store',
            ]);

            $this->app->put('/oauth/clients/{client_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@update',
            ]);

            $this->app->delete('/oauth/clients/{client_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@destroy',
            ]);
        });
    }

    /**
     * Register the routes needed for managing personal access tokens.
     *
     * @return void
     */
    public function forPersonalAccessTokens()
    {
        $this->app->group(['middleware' => ['auth']], function () {
            $this->app->get('/oauth/scopes', [
                'uses' => '\Laravel\Passport\Http\Controllers\ScopeController@all',
            ]);

            $this->app->get('/oauth/personal-access-tokens', [
                'uses' => '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@forUser',
            ]);

            $this->app->post('/oauth/personal-access-tokens', [
                'uses' => '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@store',
            ]);

            $this->app->delete('/oauth/personal-access-tokens/{token_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@destroy',
            ]);
        });
    }
}
