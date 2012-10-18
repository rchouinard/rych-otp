<?php

namespace OTP\Test;

use OTP\HOTP;

require_once 'OTP\HOTP.php';

class HOTPTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testGenerateSeed()
    {
        $hotp = new HOTP;

        $seed = $hotp->generateSeed(10);
        $this->assertRegExp('/^[A-Z2-7]{16}$/', $seed);
        $this->assertEquals($seed, $hotp->getSeed());

        $seed = $hotp->generateSeed(20);
        $this->assertRegExp('/^[A-Z2-7]{32}$/', $seed);
        $this->assertEquals($seed, $hotp->getSeed());
    }

    /**
     * @test
     */
    public function testGenerateOTP()
    {
        $hotp = new HOTP;

        $hotp->setSeed('22K3UFWSQLCDGNXH');
        $this->assertEquals('466474', $hotp->generateOTP(45016826));
        $this->assertEquals('451890', $hotp->generateOTP('1'));
        $this->assertEquals('951917', $hotp->generateOTP('2'));
        $this->assertEquals('42114359', $hotp->generateOTP('123456', 8));

        $hotp->setSeed('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ');
        $this->assertEquals('616632', $hotp->generateOTP(45016826));
        $this->assertEquals('287082', $hotp->generateOTP('1'));
        $this->assertEquals('359152', $hotp->generateOTP('2'));
        $this->assertEquals('96746508', $hotp->generateOTP('123456', 8));
    }

    /**
     * @test
     */
    public function testVerifyOTP()
    {
        $hotp = new HOTP;

        $hotp->setSeed('22K3UFWSQLCDGNXH');
        // Test that look-ahead counter correction works as expected
        // Counter values higher than expected, but still within the window,
        // should work. Lower values should not, lest we find that old OTPs
        // can be reused.
        //
        // Counter value = 100, OTP generated with 100
        $this->assertTrue('100' == $hotp->verifyOTP('785234', '100'));

        // Counter value = 98, OTP generated with 100
        $this->assertTrue('100' == $hotp->verifyOTP('785234', '98', 4));

        // Counter value = 101, OTP generated with 100
        $this->assertFalse((bool) $hotp->verifyOTP('785234', '101', 4));
    }

}
