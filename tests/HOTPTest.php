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

    /**
     * Data provider for test vectors
     *
     * @return array
     */
    public function getTestVectors()
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
     * Test that the calculate method produces OTP values expected by RFC 4226
     *
     * @test
     * @dataProvider getTestVectors()
     * @param  integer $counter
     * @param  string  $otp
     * @return void
     */
    public function testCalculateMethodProducesExpectedValues($counter, $otp)
    {
        $hotp = new HOTP(self::SECRET);
        $this->assertEquals($otp, $hotp->calculate($counter));
    }

    /**
     * Test that the validate method validates OTP values expected by RFC 4226
     *
     * @test
     * @dataProvider getTestVectors()
     * @param  integer $counter
     * @param  string  $otp
     * @return void
     */
    public function testValidateMethodValidatesExpectedValues($counter, $otp)
    {
        $hotp = new HOTP(self::SECRET);
        $this->assertTrue($hotp->verify($otp, $counter));
    }

    /**
     * Test that the validate method validates OTP values inside window
     *
     * This test will check that a token which is ahead of the application"s
     * counter can still be validated. This can happen if the user refreshes
     * the token (requests a new OTP) unnecessarily.
     *
     * @test
     * @return void
     */
    public function testValidateMethodValidatesValuesInsideWindow()
    {
        // Window of 1, meaning we"ll allow the token to be ahead by no
        // more than one.
        $hotp = new HOTP(self::SECRET, ["window" => 1]);

        // Token ahead by one (inside of window)
        $otp = "359152"; // Token counter value is 2
        $counter = 1;    // Stored counter value is 1
        $this->assertTrue($hotp->verify($otp, $counter));
        $this->assertEquals(1, $hotp->getLastValidCounterOffset());
    }

    /**
     * Test that the validate method rejects OTP values outside window
     *
     * This test will check that a token which is too far ahead of the
     * application"s counter will be rejected. This can happen if the user
     * refreshes the token (requests a new OTP) unnecessarily too many times.
     *
     * @test
     * @return void
     */
    public function testValidateMethodRejectsValuesOutsideWindow()
    {
        // Window of 1, meaning we"ll allow the token to be ahead by no
        // more than one.
        $hotp = new HOTP(self::SECRET, ["window" => 1]);

        // Token ahead by two (outside of window)
        $otp = "359152"; // Token counter value is 2
        $counter = 0;    // Stored counter value is 0
        $this->assertFalse($hotp->verify($otp, $counter));
        $this->assertNull($hotp->getLastValidCounterOffset());
    }
}
