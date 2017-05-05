<?php

namespace Dusterio\LumenPassport;

class RouteRegistrar
{
    /**
     * Application
     */
    private $app;

    /**
     * Create a new route registrar instance.
     *
     * @param  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Register routes for transient tokens, clients, and personal access tokens.
     *
     * @return void
     */
    public function all()
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
        $this->app->post('/token', [
            'uses' => '\Dusterio\LumenPassport\Http\Controllers\AccessTokenController@issueToken'
        ]);

        $this->app->group(['middleware' => ['auth']], function () {
            $this->app->get('/tokens', [
                'uses' => '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@forUser',
            ]);

            $this->app->delete('/tokens/{token_id}', [
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
        $this->app->post('/token/refresh', [
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
            $this->app->get('/clients', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@forUser',
            ]);

            $this->app->post('/clients', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@store',
            ]);

            $this->app->put('/clients/{client_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\ClientController@update',
            ]);

            $this->app->delete('/clients/{client_id}', [
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
            $this->app->get('/scopes', [
                'uses' => '\Laravel\Passport\Http\Controllers\ScopeController@all',
            ]);

            $this->app->get('/personal-access-tokens', [
                'uses' => '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@forUser',
            ]);

            $this->app->post('/personal-access-tokens', [
                'uses' => '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@store',
            ]);

            $this->app->delete('/personal-access-tokens/{token_id}', [
                'uses' => '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@destroy',
            ]);
        });
    }
}
