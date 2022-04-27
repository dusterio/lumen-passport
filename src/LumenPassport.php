<?php

namespace Dusterio\LumenPassport;

use Illuminate\Support\Arr;
use Laravel\Passport\Passport;
use DateTimeInterface;
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
     * The date when refresh tokens expire.
     *
     * @var \DateTimeInterface|null
     */
    public static $refreshTokensExpireAt;

    /**
     * The date when personal access tokens expire.
     *
     * @var \DateTimeInterface|null
     */
    public static $personalAccessTokensExpireAt;

    /**
     * Instruct Passport to keep revoked tokens pruned.
     */
    public static function allowMultipleTokens()
    {
        static::$allowMultipleTokens = true;
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
        }

        static::$tokensExpireAt[$clientId] = $date;

        return new static;
    }

    /**
     * Get or set when refresh tokens expire.
     *
     * @param  \DateTimeInterface|null  $date
     * @param int $clientId
     * @return \DateInterval|static
     */
    public static function refreshTokensExpireIn(DateTimeInterface $date = null, $clientId = null)
    {
        if (! $clientId) return Passport::refreshTokensExpireIn($date);

        if (is_null($date)) {
            return isset(static::$refreshTokensExpireAt[$clientId])
                ? Carbon::now()->diff(static::$refreshTokensExpireAt[$clientId])
                : Passport::refreshTokensExpireIn();
        }

        static::$refreshTokensExpireAt[$clientId] = $date;

        return new static;
    }

    /**
     * Get or set when personal access tokens expire.
     *
     * @param  \DateTimeInterface|null  $date
     * @param int $clientId
     * @return \DateInterval|static
     */
    public static function personalAccessTokensExpireIn(DateTimeInterface $date = null, $clientId = null)
    {
        if (! $clientId) return Passport::personalAccessTokensExpireIn($date);

        if (is_null($date)) {
            return isset(static::$personalAccessTokensExpireAt[$clientId])
                ? Carbon::now()->diff(static::$personalAccessTokensExpireAt[$clientId])
                : Passport::personalAccessTokensExpireIn();
        }

        static::$personalAccessTokensExpireAt[$clientId] = $date;

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
        if ($callback instanceof Application && preg_match('/(5\.[5-8]\..*)|(6\..*)|(7\..*)|(8\..*)/', $callback->version())) $callback = $callback->router;

        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $defaultOptions = [
            'prefix' => 'oauth',
            'namespace' => '\Laravel\Passport\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        $callback->group(Arr::except($options, ['namespace']), function ($router) use ($callback, $options) {
            $routes = new RouteRegistrar($router, $options);
            $routes->all();
        });
    }
}
