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
        return [
            // Encoded, Decoded
            ["", ""],
            ["f", "f"],
            ["fo", "fo"],
            ["foo", "foo"],
            ["foob", "foob"],
            ["fooba", "fooba"],
            ["foobar", "foobar"],
        ];
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
