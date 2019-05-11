<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

namespace Rych\OTP\Tests;

use PHPUnit\Framework\TestCase;
use Rych\OTP\HOTP;

/**
 * RFC-4226 HMAC-Based One-Time Password Tests
 */
class HOTPTest extends TestCase
{
    protected const SECRET = "12345678901234567890";

    public function vectorProvider() : array
    {
        return [
            // Adapted from RFC 4226
            [0, "755224"],
            [1, "287082"],
            [2, "359152"],
            [3, "969429"],
            [4, "338314"],
            [5, "254676"],
            [6, "287922"],
            [7, "162583"],
            [8, "399871"],
            [9, "520489"],
        ];
    }

    /**
     * @test
     * @dataProvider vectorProvider()
     *
     * @param   int     $counter
     * @param   string  $otp
     */
    public function calculateMethodProducesExpectedValues(int $counter, string $otp) : void
    {
        $hotp = new Hotp(self::SECRET);

        $this->assertEquals($otp, $hotp->calculate($counter));
    }

    /**
     * @test
     * @dataProvider vectorProvider()
     *
     * @param   int     $counter
     * @param   string  $otp
     */
    public function verifyMethodVerifiesValidOtp(int $counter, string $otp) : void
    {
        $hotp = new Hotp(self::SECRET);

        $this->assertTrue($hotp->verify($otp, $counter));
    }

    /**
     * @test
     */
    public function verifyMethodHandlesWindowCorrectly() : void
    {
        // Allow 2 tokens before and after counter
        $hotp = new Hotp(self::SECRET, ["window" => 2]);

        $counter = 100;
        $otp = $hotp->calculate($counter);

        // Within window
        $this->assertTrue($hotp->verify($otp, $counter - 1));
        $this->assertEquals(-1, $hotp->getLastOffset());
        $this->assertTrue($hotp->verify($otp, $counter + 1));
        $this->assertEquals(1, $hotp->getLastOffset());

        // Outside window
        $this->assertFalse($hotp->verify($otp, $counter - 3));
        $this->assertEquals(null, $hotp->getLastOffset());
        $this->assertFalse($hotp->verify($otp, $counter + 3));
        $this->assertEquals(null, $hotp->getLastOffset());
    }
}
