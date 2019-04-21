<?php

namespace Rych\OTP\Encoder;

use Rych\OTP\Encoder\Exception\RuntimeException;

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

    public function invalidDataProvider() : array
    {
        return array (
            // Encoded, Decoded
            array ("ABCDEFG"), // not multiple of 8 (1)
            array ("ABCDEFGHIJKL"), // not multiple of 8 (2)
            array ("1nV@liD!"), // invalid characters
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

    /**
     * @test
     * @dataProvider invalidDataProvider()
     */
    public function invalidDataThrowsRuntimeException($data) : void
    {
        $this->expectException(RuntimeException::class);
        $this->encoder->decode($data);
    }
}
