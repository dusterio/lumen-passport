<?php

namespace Dusterio\LumenPassport;

use Laravel\Passport\Passport;
use DateTimeInterface;
use DateInterval;
use Carbon\Carbon;
use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;

class LumenPassport
{
    /**
     * Allow simultaneous logins for users
     *
     * @var bool
     */
    public static $allowMultipleTokens = false;

    /**
     * The date when access tokens expire (specific per password client).
     *
     * @var array
     */
    public static $tokensExpireAt = [];

    /**
     * Instruct Passport to keep revoked tokens pruned.
     */
    public static function allowMultipleTokens()
    {
        static::$allowMultipleTokens = true;
    }

    /**
     * Delete older tokens or just mark them as revoked?
     */
    public static function prunePreviousTokens()
    {
        Passport::pruneRevokedTokens();
    }

    /**
     * Get or set when access tokens expire.
     *
     * @param  \DateTimeInterface|null  $date
     * @param int $clientId
     * @return \DateInterval|static
     */
    public static function tokensExpireIn(DateTimeInterface $date = null, $clientId = null)
    {
        if (! $clientId) return Passport::tokensExpireIn($date);

        if (is_null($date)) {
            return isset(static::$tokensExpireAt[$clientId])
                ? Carbon::now()->diff(static::$tokensExpireAt[$clientId])
                : Passport::tokensExpireIn();
        } else {
            static::$tokensExpireAt[$clientId] = $date;
        }

        return new static;
    }

    /**
     * Get a Passport route registrar.
     *
     * @param  callable|Router|Application  $callback
     * @param  array  $options
     * @return RouteRegistrar
     */
    public static function routes($callback = null, array $options = [])
    {
        if ($callback instanceof Application && preg_match('/5\.[56]\..*/', $callback->version())) $callback = $callback->router;

        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $defaultOptions = [
            'prefix' => 'oauth',
            'namespace' => '\Laravel\Passport\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        $callback->group(array_except($options, ['namespace']), function ($router) use ($callback, $options) {
            $routes = new RouteRegistrar($router, $options);
            $routes->all();
        });
    }
}
