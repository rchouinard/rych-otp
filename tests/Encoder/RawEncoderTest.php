<?php

namespace Rych\OTP\Encoder;

class RawEncoderTest extends \PHPUnit\Framework\TestCase
{
    private $encoder;

    protected function setUp() : void
    {
        $this->encoder = new RawEncoder();
    }

    public function vectorProvider() : array
    {
        return array (
            // Encoded, Decoded
            array ('', ''),
            array ('f', 'f'),
            array ('fo', 'fo'),
            array ('foo', 'foo'),
            array ('foob', 'foob'),
            array ('fooba', 'fooba'),
            array ('foobar', 'foobar'),
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
