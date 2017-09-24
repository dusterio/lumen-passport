<?php

namespace Dusterio\LumenPassport;

class RouteRegistrar
{
    /**
     * \Laravel\Lumen\Routing\Router Router
     */
    private $router;

    /**
     * Create a new route registrar instance.
     *
     * @param  \Laravel\Lumen\Routing\Router $router
     * @return void
     */
    public function __construct(\Laravel\Lumen\Routing\Router $router)
    {
        $this->router = $router;
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
        $this->router->post('/token', [
            'uses' => 'AccessTokenController@issueToken',
            'namespace' => '\Dusterio\LumenPassport\Http\Controllers'
        ]);

        $this->router->group(['middleware' => ['auth']], function () {
            $this->router->get('/tokens', [
                'uses' => 'AuthorizedAccessTokenController@forUser',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->router->delete('/tokens/{token_id}', [
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
        $this->router->post('/token/refresh', [
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
        $this->router->group(['middleware' => ['auth']], function () {
            $this->router->get('/clients', [
                'uses' => 'ClientController@forUser',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->router->post('/clients', [
                'uses' => 'ClientController@store',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->router->put('/clients/{client_id}', [
                'uses' => 'ClientController@update',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->router->delete('/clients/{client_id}', [
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
        $this->router->group(['middleware' => ['auth']], function () {
            $this->router->get('/scopes', [
                'uses' => 'ScopeController@all',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->router->get('/personal-access-tokens', [
                'uses' => 'PersonalAccessTokenController@forUser',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->router->post('/personal-access-tokens', [
                'uses' => 'PersonalAccessTokenController@store',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);

            $this->router->delete('/personal-access-tokens/{token_id}', [
                'uses' => 'PersonalAccessTokenController@destroy',
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ]);
        });
    }
}
