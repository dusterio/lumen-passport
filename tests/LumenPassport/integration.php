<?php

namespace Dusterio\LumenPassport\Tests;

use Carbon\Carbon;
use Dusterio\LumenPassport\LumenPassport;
use Laravel\Passport\Passport;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class IntegrationTest
 * @package Dusterio\LumenPassport\Tests
 */
class IntegrationTest extends BaseTestCase
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