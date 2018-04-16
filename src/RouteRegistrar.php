<?php

namespace Dusterio\LumenPassport;

class RouteRegistrar
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $options;

    /**
     * Create a new route registrar instance.
     *
     * @param  $app
     * @param  array $options
     */
    public function __construct($app, array $options = [])
    {
        $this->app = $app;
        $this->options = $options;
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
     * @param string $path
     * @return string
     */
    private function prefix($path)
    {
        if (strstr($path, '\\') === false && isset($this->options['namespace'])) return $this->options['namespace'] . '\\' . $path;

        return $path;
    }

    /**
     * Register the routes for retrieving and issuing access tokens.
     *
     * @return void
     */
    public function forAccessTokens()
    {
        $this->app->router->post('/token', $this->prefix('\Dusterio\LumenPassport\Http\Controllers\AccessTokenController@issueToken'));

        $this->app->router->group(['middleware' => ['auth']], function () {
            $this->app->router->get('/tokens', $this->prefix('AuthorizedAccessTokenController@forUser'));
            $this->app->router->delete('/tokens/{token_id}', $this->prefix('AuthorizedAccessTokenController@destroy'));
        });
    }

    /**
     * Register the routes needed for refreshing transient tokens.
     *
     * @return void
     */
    public function forTransientTokens()
    {
        $this->app->router->post('/token/refresh', [
            'middleware' => ['auth'],
            'uses' => $this->prefix('TransientTokenController@refresh')
        ]);
    }

    /**
     * Register the routes needed for managing clients.
     *
     * @return void
     */
    public function forClients()
    {
        $this->app->router->group(['middleware' => ['auth']], function () {
            $this->app->router->get('/clients', $this->prefix('ClientController@forUser'));
            $this->app->router->post('/clients', $this->prefix('ClientController@store'));
            $this->app->router->put('/clients/{client_id}', $this->prefix('ClientController@update'));
            $this->app->router->delete('/clients/{client_id}', $this->prefix('ClientController@destroy'));
        });
    }

    /**
     * Register the routes needed for managing personal access tokens.
     *
     * @return void
     */
    public function forPersonalAccessTokens()
    {
        $this->app->router->group(['middleware' => ['auth']], function () {
            $this->app->router->get('/scopes', $this->prefix('ScopeController@all'));
            $this->app->router->get('/personal-access-tokens', $this->prefix('PersonalAccessTokenController@forUser'));
            $this->app->router->post('/personal-access-tokens', $this->prefix('PersonalAccessTokenController@store'));
            $this->app->router->delete('/personal-access-tokens/{token_id}', $this->prefix('PersonalAccessTokenController@destroy'));
        });
    }
}
