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
            'uses' => 'AccessTokenController@issueToken',
            'namespace' => '\Dusterio\LumenPassport\Http\Controllers'
        ]);

        $this->app->group(['middleware' => ['auth']], function () {
            $this->app->get('/tokens', [
                'uses' => 'AuthorizedAccessTokenController@forUser',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->app->delete('/tokens/{token_id}', [
                'uses' => 'AuthorizedAccessTokenController@destroy',
                'namespace' => '\Laravel\Passport\Http\Controllers'
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
            'uses' => 'TransientTokenController@refresh',
            'namespace' => '\Laravel\Passport\Http\Controllers'
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
                'uses' => 'ClientController@forUser',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->app->post('/clients', [
                'uses' => 'ClientController@store',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->app->put('/clients/{client_id}', [
                'uses' => 'ClientController@update',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->app->delete('/clients/{client_id}', [
                'uses' => 'ClientController@destroy',
                'namespace' => '\Laravel\Passport\Http\Controllers'
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
                'uses' => 'ScopeController@all',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->app->get('/personal-access-tokens', [
                'uses' => 'PersonalAccessTokenController@forUser',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->app->post('/personal-access-tokens', [
                'uses' => 'PersonalAccessTokenController@store',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->app->delete('/personal-access-tokens/{token_id}', [
                'uses' => 'PersonalAccessTokenController@destroy',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);
        });
    }
}
