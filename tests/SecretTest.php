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

use Rych\Otp\Secret;

class SecretTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canDetectHexValue()
    {
        $secret = new Secret("736563726574313233");
        $this->assertSame("secret123", $secret->getValue());
    }

    /**
     * @test
     */
    public function canDetectBase32Value()
    {
        $secret = new Secret("ONSWG4TFOQYTEMY=");
        $this->assertSame("secret123", $secret->getValue());
    }

    /**
     * @test
     */
    public function canDetectRawValue()
    {
        $secret = new Secret("secret123");
        $this->assertSame("secret123", $secret->getValue());
    }
}
