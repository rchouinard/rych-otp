<?php
/**
 * This file is part of Rych\OATH-OTP
 *
 * (c) Ryan Chouinard <rchouinard@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rych\Otp\Test;

use Rych\Otp\HotpProvider;
use Rych\Otp\Secret;

class HotpProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Secret
     */
    protected $secret;

    protected function setUp()
    {
        $this->secret = new Secret("12345678901234567890", Secret::FORMAT_RAW);
    }

    /**
     * @test
     */
    public function generatesExpectedValues()
    {
        $provider = new HotpProvider($this->secret);
        $this->assertEquals("755224", $provider->generate(0));
        $this->assertEquals("287082", $provider->generate(1));
        $this->assertEquals("359152", $provider->generate(2));
    }

    /**
     * @test
     */
    public function validatesAsExpected()
    {
        $provider = new HotpProvider($this->secret, ["window" => 1]);
        $this->assertEquals(0, $provider->validate("755224", 0));
        $this->assertEquals(1, $provider->validate("287082", 0));
        $this->assertFalse($provider->validate("359152", 0));
    }
}
