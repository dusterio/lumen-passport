<?php

namespace Dusterio\LumenPassport\Tests;

use Dusterio\LumenPassport\LumenPassport;
use Carbon\Carbon;
use Laravel\Passport\Passport;
use PHPUnit\Framework\TestCase;

/**
 * Class IntegrationTest
 * @package Dusterio\LumenPassport\Tests
 */
class IntegrationTest extends TestCase
{

    /**
     * @test
     */
    public function global_token_ttl_can_be_set_via_lumen_class() {
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $expiryDate = $now->clone()->addYear();
        LumenPassport::tokensExpireIn($expiryDate);
        $this->assertEquals(Passport::tokensExpireIn(), Carbon::now()->diff($expiryDate));
        $this->assertEquals(LumenPassport::tokensExpireIn(), Carbon::now()->diff($expiryDate));
    }

    /**
     * @test
     */
    public function client_specific_token_ttl_can_be_set_via_lumen_class()
    {
        $clientId = 2;
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $clientExpiryDate = $now->clone()->addYears(5);
        $defaultGlobalExpiryDate = $now->clone()->addYears(1);

        LumenPassport::tokensExpireIn($clientExpiryDate, $clientId);
        $this->assertEquals(LumenPassport::tokensExpireIn(null, $clientId), Carbon::now()->diff($clientExpiryDate));

        # global TTL should still default to 1 year
        $this->assertEquals(LumenPassport::tokensExpireIn(), Carbon::now()->diff($defaultGlobalExpiryDate));
        $this->assertEquals(Passport::tokensExpireIn(), Carbon::now()->diff($defaultGlobalExpiryDate));
    }
}