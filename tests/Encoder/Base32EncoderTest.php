<?php

namespace Rych\OTP\Encoder;

class Base32EncoderTest extends \PHPUnit\Framework\TestCase
{
    private $encoder;

    protected function setUp() : void
    {
        $this->encoder = new Base32Encoder();
    }

    public function vectorProvider() : array
    {
        return array (
            // Encoded, Decoded
            array ('', ''),
            array ('MY======', 'f'),
            array ('MZXQ====', 'fo'),
            array ('MZXW6===', 'foo'),
            array ('MZXW6YQ=', 'foob'),
            array ('MZXW6YTB', 'fooba'),
            array ('MZXW6YTBOI======', 'foobar'),
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
