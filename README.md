# Lumen Passport

[![Build Status](https://travis-ci.org/dusterio/lumen-passport.svg)](https://travis-ci.org/dusterio/lumen-passport)
[![Code Climate](https://codeclimate.com/github/dusterio/lumen-passport/badges/gpa.svg)](https://codeclimate.com/github/dusterio/lumen-passport/badges)
[![Total Downloads](https://poser.pugx.org/dusterio/lumen-passport/d/total.svg)](https://packagist.org/packages/dusterio/lumen-passport)
[![Latest Stable Version](https://poser.pugx.org/dusterio/lumen-passport/v/stable.svg)](https://packagist.org/packages/dusterio/lumen-passport)
[![Latest Unstable Version](https://poser.pugx.org/dusterio/lumen-passport/v/unstable.svg)](https://packagist.org/packages/dusterio/lumen-passport)
[![License](https://poser.pugx.org/dusterio/lumen-passport/license.svg)](https://packagist.org/packages/dusterio/lumen-passport)

> Making Laravel Passport work with Lumen

## Introduction

It's a simple service provider that makes **Laravel Passport** work with **Lumen**.

## Installation

First install [Lumen Micro-Framework](https://github.com/laravel/lumen) if you don't have it yet.

Then install **Lumen Passport**:

```bash
composer require dusterio/lumen-passport
```

Or if you prefer, edit `composer.json` manually and run then `composer update`:

```json
{
    "require": {
        "dusterio/lumen-passport": "^0.3.5"
    }
}
```

### Modify the bootstrap flow

We need to enable both **Laravel Passport** provider and **Lumen Passport** specific provider:

```php
/** @file bootstrap/app.php */

// Enable Facades
$app->withFacades();

// Enable Eloquent
$app->withEloquent();

// Enable auth middleware (shipped with Lumen)
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);

// Register two service providers, Laravel Passport and Lumen adapter
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
```

### Laravel Passport ^7.3.2 and newer

On 30 Jul 2019 [Laravel Passport 7.3.2](https://github.com/laravel/passport/releases/tag/v7.3.2) had a breaking change - new method introduced on Application class that exists in Laravel but not in Lumen. You could either lock in to an older version or swap the Application class like follows:

```php
/** @file bootstrap/app.php */

//$app = new Laravel\Lumen\Application(
//    dirname(__DIR__)
//);
$app = new \Dusterio\LumenPassport\Lumen7Application(
    dirname(__DIR__)
);
```

\* _Note: If you look inside this class - all it does is adding an extra method `configurationIsCached()` that always returns `false`._

### Migrate and install Laravel Passport

```bash
# Create new tables for Passport
php artisan migrate

# Install encryption keys and other stuff for Passport
php artisan passport:install
```

It will output the Personal access client ID and secret, and the Password grand client ID and secret.

\* _Note: Save the secrets in a safe place, you'll need them later to request the access tokens._

## Configuration

### Configure Authentication

Edit `config/auth.php` to suit your needs. A simple example:

```php
/** @file config/auth.php */

return [

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ]
    ],

];
```

\* _Note: Lumen 7.x and older uses `\App\User::class`_

Load the config since Lumen doesn't load config files automatically:

```php
/** @file bootstrap/app.php */

$app->configure('auth');
```

### Registering Routes

Next, you should call the `LumenPassport::routes` method within the `boot` method of your application (one of your service providers). This method will register the routes necessary to issue access tokens and revoke access tokens, clients, and personal access tokens:

```php
/** @file app/Providers/AuthServiceProvider.php */

use Dusterio\LumenPassport\LumenPassport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        LumenPassport::routes($this->app);

        /* rest of boot */
    }
}
```

### User model

Make sure your user model uses **Laravel Passport**'s `HasApiTokens` trait.

```php
/** @file app/Models/User.php */

use Laravel\Passport\HasApiTokens;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable, HasFactory;

    /* rest of the model */
}
```

## Usage

You'll find all the documentation in [Laravel Passport Docs](https://laravel.com/docs/master/passport).

### Curl example with username and password authentication

First you have to [issue an access token](https://laravel.com/docs/master/passport#issuing-access-tokens) and then you can use it to authenticate your requests.

```bash
# Request
curl --location --request POST '{{APP_URL}}/oauth/token' \
--header 'Content-Type: application/json' \
--data-raw '{
    "grant_type": "password",
    "client_id": "{{CLIENT_ID}}",
    "client_secret": "{{CLIENT_SECRET}}",
    "username": "{{USER_EMAIL}}",
    "password": "{{USER_PASSWORD}}",
    "scope": "*"
}'
```

```json
{
    "token_type": "Bearer",
    "expires_in": 31536000,
    "access_token": "******",
    "refresh_token": "******"
}
```

And with the `access_token` you can request access to the routes that uses the Auth:Api Middleware provided by the **Lumen Passport**.

```php
/** @file routes/web.php */

$router->get('/ping', ['middleware' => 'auth', fn () => 'pong']);
```

```bash
# Request
curl --location --request GET '{{APP_URL}}/ping' \
--header 'Authorization: Bearer {{ACCESS_TOKEN}}'
```

```html
pong
```

### Installed routes

This package mounts the following routes after you call `routes()` method, all of them belongs to the namespace `\Laravel\Passport\Http\Controllers`:

Verb | Path | Controller | Action | Middleware
--- | --- | --- | --- | ---
POST   | /oauth/token                             | AccessTokenController           | issueToken | -
GET    | /oauth/tokens                            | AuthorizedAccessTokenController | forUser    | auth
DELETE | /oauth/tokens/{token_id}                 | AuthorizedAccessTokenController | destroy    | auth
POST   | /oauth/token/refresh                     | TransientTokenController        | refresh    | auth
GET    | /oauth/clients                           | ClientController                | forUser    | auth
POST   | /oauth/clients                           | ClientController                | store      | auth
PUT    | /oauth/clients/{client_id}               | ClientController                | update     | auth
DELETE | /oauth/clients/{client_id}               | ClientController                | destroy    | auth
GET    | /oauth/scopes                            | ScopeController                 | all        | auth
GET    | /oauth/personal-access-tokens            | PersonalAccessTokenController   | forUser    | auth
POST   | /oauth/personal-access-tokens            | PersonalAccessTokenController   | store      | auth
DELETE | /oauth/personal-access-tokens/{token_id} | PersonalAccessTokenController   | destroy    | auth

\* _Note: some of the **Laravel Passport**'s routes had to 'go away' because they are web-related and rely on sessions (eg. authorise pages). Lumen is an API framework so only API-related routes are present._

## Extra features

There are a couple of extra features that aren't present in **Laravel Passport**

### Prefixing Routes

You can add that into an existing group, or add use this route registrar independently like so;

```php
/** @file app/Providers/AuthServiceProvider.php */

use Dusterio\LumenPassport\LumenPassport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        LumenPassport::routes($this->app, ['prefix' => 'v1/oauth']);

        /* rest of boot */
    }
}
```

### Multiple tokens per client

Sometimes it's handy to allow multiple access tokens per password grant client. Eg. user logs in from several browsers
simultaneously. Currently **Laravel Passport** does not allow that.

```php
/** @file app/Providers/AuthServiceProvider.php */

use Dusterio\LumenPassport\LumenPassport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        LumenPassport::routes($this->app);
        LumenPassport::allowMultipleTokens();

        /* rest of boot */
    }
}
```

### Different TTLs for different password clients

**Laravel Passport** allows to set one global TTL (time to live) for access tokens, but it may be useful sometimes to set different TTLs for different clients (eg. mobile users get more time than desktop users).

Simply do the following in your service provider:

```php
/** @file app/Providers/AuthServiceProvider.php */

use Carbon\Carbon;
use Dusterio\LumenPassport\LumenPassport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        LumenPassport::routes($this->app);
        $client_id = '1';
        LumenPassport::tokensExpireIn(Carbon::now()->addDays(14), $client_id); 

        /* rest of boot */
    }
}
```

If you don't specify client Id, it will simply fall back to Laravel Passport implementation.

### Purge expired tokens

```bash
php artisan passport:purge
```

Simply run it to remove expired refresh tokens and their corresponding access tokens from the database.

## Error and issue resolution

Instead of opening a new issue, please see if someone has already had it and it has been resolved.

If you have found a bug or want to contribute to improving the package, please review the [Contributing guide](https://github.com/dusterio/lumen-passport/blob/master/CONTRIBUTING.md) and the [Code of Conduct](https://github.com/dusterio/lumen-passport/blob/master/CODE_OF_CONDUCT.md).

## Video tutorials

I've just started a educational YouTube channel [config.sys](https://www.youtube.com/channel/UCIvUJ1iVRjJP_xL0CD7cMpg) that will cover top IT trends in software development and DevOps.

Also I'm happy to announce my newest tool – [GrammarCI](https://www.grammarci.com/), an automated (as a part of CI/CD process) spelling and grammar checks for your code so that your users don't see your typos :)

## License

The MIT License (MIT)
Copyright (c) 2016 Denis Mysenko

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
