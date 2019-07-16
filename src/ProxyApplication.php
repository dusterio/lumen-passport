<?php

namespace Dusterio\LumenPassport;

class ProxyApplication implements \ArrayAccess {
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @return bool
     */
    public function configurationIsCached()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function runningInConsole()
    {
        return $this->app->runningInConsole();
    }

    /**
     * @param string $symbol
     * @param callable|mixed $callback
     * @return mixed
     */
    public function singleton($symbol, $callback)
    {
        return $this->app->singleton($symbol, $callback);
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->app->offsetExists($key);
    }

    /**
     * Get the value at a given offset.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->app->offsetGet($key);
    }

    /**
     * Set the value at a given offset.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->app->offsetSet($key, $value);
    }

    /**
     * Unset the value at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->app->offsetUnset($key);
    }

    /**
     * Dynamically access container services.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->app[$key];
    }

    /**
     * Dynamically set container services.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->app[$key] = $value;
    }
}