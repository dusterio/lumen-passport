<?php

namespace Dusterio\LumenPassport\Tests;

use Dusterio\LumenPassport\Http\Controllers\AccessTokenController;
use Dusterio\LumenPassport\LumenPassport;
use Carbon\Carbon;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;

/**
 * Class IntegrationTest
 * @package Dusterio\LumenPassport\Tests
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function token_ttl_can_be_set_via_lumen_class()
    {
        // Default (global) client
        LumenPassport::tokensExpireIn(Carbon::now()->addYears(1));
        $this->assertTrue(Passport::tokensExpireIn() == Carbon::now()->diff(Carbon::now()->addYears(1)));
        $this->assertTrue(LumenPassport::tokensExpireIn() == Carbon::now()->diff(Carbon::now()->addYears(1)));

        // Specific client
        LumenPassport::tokensExpireIn(Carbon::now()->addYears(5), 2);
        $this->assertTrue(LumenPassport::tokensExpireIn(null, 2) == Carbon::now()->diff(Carbon::now()->addYears(5)));
        $this->assertTrue(LumenPassport::tokensExpireIn() == Carbon::now()->diff(Carbon::now()->addYears(1)));
        $this->assertTrue(Passport::tokensExpireIn() == Carbon::now()->diff(Carbon::now()->addYears(1)));
    }
}