<?php

namespace Rych\OTP\Test;

use PHPUnit_Framework_TestCase as TestCase;
use Rych\OTP\TOTP;
use Rych\OTP\Seed;

class TOTPTest extends TestCase
{

    /**
     * @var Rych\OTP\TOTP
     */
    private $totp;

    /**
     * Set up test environment
     *
     * @return void
     */
    public function setUp()
    {
        $this->totp = new TOTP(new Seed('3132333435363738393031323334353637383930'));
    }

    /**
     * Data provider for test vectors
     *
     * @return array
     */
    public function getTestVectors()
    {
        return array (
            // Adapted from RFC 6238
            array (strtotime('1970-01-01 00:00:59 UTC'), '287082'),
            array (strtotime('2005-03-18 01:58:29 UTC'), '081804'),
            array (strtotime('2005-03-18 01:58:31 UTC'), '050471'),
            array (strtotime('2009-02-13 23:31:30 UTC'), '005924'),
            array (strtotime('2033-05-18 03:33:20 UTC'), '279037'),
            //array (strtotime('2603-10-11 11:33:20 UTC'), '353130'),
        );
    }

    /**
     * Test that test vectors are properly calculated
     *
     * @dataProvider getTestVectors()
     * @test
     * @return void
     */
    public function testTestVectors($counter, $otp)
    {
        $this->assertEquals($otp, $this->totp->calculate($counter), 'Calculate method failed to produce expected result.');
        $this->assertTrue($this->totp->validate($otp, $counter), 'Validate method failed to produce expected result.');
    }

    /**
     * Test that OTP verification windows work
     *
     * @test
     * @return void
     */
    public function testValidateMethodRespondsToWindowParameter()
    {
        $counter = strtotime('2013-01-01 00:00:00 UTC');
        $this->totp->setWindow(4);

        // Counter offset = 0
        $this->assertTrue($this->totp->validate('942875', $counter));
        $this->assertEquals(0, $this->totp->getLastValidCounterOffset());

        // Counter offset = 2
        $this->assertTrue($this->totp->validate('260746', $counter));
        $this->assertEquals(2, $this->totp->getLastValidCounterOffset());

        // Counter offset = 5
        $this->assertFalse($this->totp->validate('190945', 0));
        $this->assertNull($this->totp->getLastValidCounterOffset());

        // Invalid OTP
        $this->assertFalse($this->totp->validate('NOPE', $counter));
        $this->assertNull($this->totp->getLastValidCounterOffset());
    }

}
