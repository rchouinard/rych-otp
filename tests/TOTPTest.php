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
use Rych\OTP\TOTP;

/**
 * RFC-6238 Time-Based One-Time Password Tests
 */
class TOTPTest extends TestCase
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
            // Adapted from RFC 6238
            [strtotime("1970-01-01 00:00:59 UTC"), "287082"],
            [strtotime("2005-03-18 01:58:29 UTC"), "081804"],
            [strtotime("2005-03-18 01:58:31 UTC"), "050471"],
            [strtotime("2009-02-13 23:31:30 UTC"), "005924"],
            [strtotime("2033-05-18 03:33:20 UTC"), "279037"],
            //[strtotime("2603-10-11 11:33:20 UTC"), "353130"],
        ];
    }

    /**
     * Test that the calculate method produces OTP values expected by RFC 6238
     *
     * @test
     * @dataProvider getTestVectors()
     * @param  integer $counter
     * @param  string  $otp
     * @return void
     */
    public function testCalculateMethodProducesExpectedValues($counter, $otp)
    {
        $totp = new TOTP(self::SECRET);
        $this->assertEquals($otp, $totp->calculate($counter));
    }

    /**
     * Test that the validate method validates OTP values expected by RFC 6238
     *
     * @test
     * @dataProvider getTestVectors()
     * @param  integer $counter
     * @param  string  $otp
     * @return void
     */
    public function testValidateMethodValidatesExpectedValues($counter, $otp)
    {
        $totp = new TOTP(self::SECRET);
        $this->assertTrue($totp->verify($otp, $counter));
    }
}
