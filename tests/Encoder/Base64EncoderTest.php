<?php

namespace Rych\OTP\Encoder;

class Base64EncoderTest extends \PHPUnit\Framework\TestCase
{
    private $encoder;

    protected function setUp() : void
    {
        $this->encoder = new Base64Encoder();
    }

    public function vectorProvider() : array
    {
        return array (
            // Encoded, Decoded
            array ('', ''),
            array ('Zg==', 'f'),
            array ('Zm8=', 'fo'),
            array ('Zm9v', 'foo'),
            array ('Zm9vYg==', 'foob'),
            array ('Zm9vYmE=', 'fooba'),
            array ('Zm9vYmFy', 'foobar'),
        );
    }

    /**
     * @test
     * @dataProvider vectorProvider()
     */
    public function encodeMethodProducesExpectedResult(string $encoded, string $decoded) : void
    {
        $this->assertEquals($encoded, $this->encoder->encode($decoded));
    }

    /**
     * @test
     * @dataProvider vectorProvider()
     */
    public function decodeMethodProducesExpectedResult(string $encoded, string $decoded) : void
    {
        $this->assertEquals($decoded, $this->encoder->decode($encoded));
    }
}
