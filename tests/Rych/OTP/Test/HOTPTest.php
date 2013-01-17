<?php

namespace Rych\OTP\Test;

use PHPUnit_Framework_TestCase as TestCase;
use Rych\OTP\HOTP;

class HOTPTest extends TestCase
{

    private $hotp;

    public function setUp()
    {
        $this->hotp = new HOTP;
    }

    public function getRFC4226TestVectors()
    {
        return array (
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
     * @dataProvider getRFC4226TestVectors()
     */
    public function testRFC4226TestVectors($counter, $otp)
    {
        $seed = '3132333435363738393031323334353637383930';
        $this->assertEquals($otp, $this->hotp->calculate($seed, $counter));
    }

    /**
     * @dataProvider getRFC4226TestVectors()
     */
    public function testValidateRFC4226TestVectors($counter, $otp)
    {
        $seed = '3132333435363738393031323334353637383930';
        $this->assertEquals($counter, $this->hotp->validate($seed, $otp, $counter));
    }

}
