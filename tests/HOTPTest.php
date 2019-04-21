<?php
/**
 * Ryan's OATH-OTP Library
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2019, Ryan Chouinard
 * @link https://github.com/rchouinard/rych-otp
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP\Tests;

use Rych\OTP\HOTP;
use Rych\OTP\Seed;

/**
 * RFC-4226 HMAC-Based One-Time Password Tests
 */
class HOTPTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Seed
     */
    protected $seed;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->seed = new Seed("3132333435363738393031323334353637383930");
    }

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
        $hotp = new HOTP($this->seed);
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
        $hotp = new HOTP($this->seed);
        $this->assertTrue($hotp->validate($otp, $counter));
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
        $hotp = new HOTP($this->seed, ["window" => 1]);

        // Token ahead by one (inside of window)
        $otp = "359152"; // Token counter value is 2
        $counter = 1;    // Stored counter value is 1
        $this->assertTrue($hotp->validate($otp, $counter));
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
        $hotp = new HOTP($this->seed, ["window" => 1]);

        // Token ahead by two (outside of window)
        $otp = "359152"; // Token counter value is 2
        $counter = 0;    // Stored counter value is 0
        $this->assertFalse($hotp->validate($otp, $counter));
        $this->assertNull($hotp->getLastValidCounterOffset());
    }

}
