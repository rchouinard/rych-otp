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

    public function vectorProvider() : array
    {
        return [
            // Adapted from RFC 6238
            [strtotime("1970-01-01 00:00:59 UTC"), "287082"],
            [strtotime("2005-03-18 01:58:29 UTC"), "081804"],
            [strtotime("2005-03-18 01:58:31 UTC"), "050471"],
            [strtotime("2009-02-13 23:31:30 UTC"), "005924"],
            [strtotime("2033-05-18 03:33:20 UTC"), "279037"],
            [strtotime("2603-10-11 11:33:20 UTC"), "353130"],
        ];
    }

    /**
     * @test
     * @dataProvider vectorProvider()
     *
     * @param   int     $timestamp
     * @param   string  $otp
     */
    public function calculateMethodProducesExpectedValues(int $timestamp, string $otp) : void
    {
        $totp = new Totp(self::SECRET);

        $this->assertEquals($otp, $totp->calculate($timestamp));
    }

    /**
     * @test
     * @dataProvider vectorProvider()
     *
     * @param   int     $timestamp
     * @param   string  $otp
     */
    public function verifyMethodVerifiesValidOtp(int $timestamp, string $otp) : void
    {
        $totp = new Totp(self::SECRET);

        $this->assertTrue($totp->verify($otp, $timestamp));
    }

    /**
     * @test
     */
    public function verifyMethodHandlesWindowCorrectly() : void
    {
        // Allow 2 tokens (60 seconds) before and after counter
        $totp = new Totp(self::SECRET, ["window" => 2]);

        $timestamp = time();
        $otp = $totp->calculate($timestamp);

        // Within window
        $this->assertTrue($totp->verify($otp, $timestamp - 30)); // Subtract 30 seconds
        $this->assertEquals(-1, $totp->getLastOffset());
        $this->assertTrue($totp->verify($otp, $timestamp + 30)); // Add 30 seconds
        $this->assertEquals(1, $totp->getLastOffset());

        // Outside window
        $this->assertFalse($totp->verify($otp, $timestamp - 90)); // Subtract 90 seconds
        $this->assertEquals(null, $totp->getLastOffset());
        $this->assertFalse($totp->verify($otp, $timestamp + 90)); // Add 90 seconds
        $this->assertEquals(null, $totp->getLastOffset());
    }
}
