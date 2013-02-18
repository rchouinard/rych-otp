<?php

namespace Rych\OTP\Test;

use PHPUnit_Framework_TestCase as TestCase;
use Rych\OTP\HOTP;
use Rych\OTP\Seed;

class HOTPTest extends TestCase
{

    /**
     * @var Rych\OTP\HOTP
     */
    private $hotp;

    /**
     * Set up test environment
     *
     * @return void
     */
    public function setUp()
    {
        $this->hotp = new HOTP(new Seed('3132333435363738393031323334353637383930'));
    }

    /**
     * Data provider for test vectors
     *
     * @return array
     */
    public function getTestVectors()
    {
        return array (
            // Adapted from RFC 4226
            array (0, '755224'),
            array (1, '287082'),
            array (2, '359152'),
            array (3, '969429'),
            array (4, '338314'),
            array (5, '254676'),
            array (6, '287922'),
            array (7, '162583'),
            array (8, '399871'),
            array (9, '520489'),
        );
    }

    /**
     * Test that test vectors are properly calculated and verified
     *
     * @dataProvider getTestVectors()
     * @test
     * @return void
     */
    public function testTestVectors($counter, $otp)
    {
        $this->assertEquals($otp, $this->hotp->calculate($counter), 'Calculate method failed to produce expected result.');
        $this->assertTrue($this->hotp->validate($otp, $counter), 'Validate method failed to produce expected result.');
    }

    /**
     * Test that OTP verification windows work
     *
     * @test
     * @return void
     */
    public function testValidateMethodRespondsToWindowParameter()
    {
        $this->hotp->setWindow(4);

        // Counter offset = 0
        $this->assertTrue($this->hotp->validate('755224', 0));
        $this->assertEquals(0, $this->hotp->getLastValidCounterOffset());

        // Counter offset = 2
        $this->assertTrue($this->hotp->validate('359152', 0));
        $this->assertEquals(2, $this->hotp->getLastValidCounterOffset());

        // Counter offset = 5
        $this->assertFalse($this->hotp->validate('254676', 0));
        $this->assertNull($this->hotp->getLastValidCounterOffset());

        // Invalid OTP
        $this->assertFalse($this->hotp->validate('NOPE', 0));
        $this->assertNull($this->hotp->getLastValidCounterOffset());
    }

}
