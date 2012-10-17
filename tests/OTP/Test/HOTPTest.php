<?php

namespace OTP\Test;
use OTP\HOTP;

require 'OTP\HOTP.php';

class HOTPTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testVectors()
    {
        $hotp = new HOTP;
        $hotp->setSeed('22K3UFWSQLCDGNXH');
        // BO5IESBLQNUVLFUB

        $this->assertEquals('466474', $hotp->generateOTP(45016826));
    }

}
