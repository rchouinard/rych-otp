<?php

namespace OTP\Test;
use OTP\Base32;

require 'OTP\Base32.php';

class Base32Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @return array
     */
    public function vectorProvider()
    {
        return array (
            array ('', ''),
            array ('AB', 'IFBA===='),
            array ('f', 'MY======'),
            array ('fo', 'MZXQ===='),
            array ('foo', 'MZXW6==='),
            array ('foob', 'MZXW6YQ='),
            array ('fooba', 'MZXW6YTB'),
            array ('foobar', 'MZXW6YTBOI======'),
        );
    }

    /**
     * @dataProvider vectorProvider()
     * @test
     */
    public function testVectors($decoded, $encoded)
    {
        $b32 = new Base32;
        $this->assertEquals($encoded, $b32->encode($decoded), "Failed encoding string '$decoded'");
        $this->assertEquals($decoded, $b32->decode($encoded), "Failed decoding string '$encoded'");
    }

}
