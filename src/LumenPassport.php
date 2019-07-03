<?php

namespace Dusterio\LumenPassport;

use Carbon\Carbon;
use DateTimeInterface;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Route;

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
     * @param  callable|null  $callback
     * @param  array          $options
     * @return void
     */
    public static function routes($callback = null, array $options = [])
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $defaultOptions = [
            'prefix' => 'oauth',
            'namespace' => '\Laravel\Passport\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group(array_except($options, ['namespace']), function ($router) use ($callback, $options) {
            $callback(new RouteRegistrar($router, $options));
        });
    }
}
