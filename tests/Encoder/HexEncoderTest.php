<?php

namespace Rych\OTP\Encoder;

use Rych\OTP\Encoder\Exception\RuntimeException;

class HexEncoderTest extends \PHPUnit\Framework\TestCase
{
    private $encoder;

    protected function setUp() : void
    {
        $this->encoder = new HexEncoder();
    }

    public function vectorProvider() : array
    {
        return array (
            // Encoded, Decoded
            array ('', ''),
            array ('66', 'f'),
            array ('666f', 'fo'),
            array ('666f6f', 'foo'),
            array ('666f6f62', 'foob'),
            array ('666f6f6261', 'fooba'),
            array ('666f6f626172', 'foobar'),
        );
    }

    public function invalidDataProvider() : array
    {
        return array (
            // Encoded, Decoded
            array ("A"), // not multiple of 2 (1)
            array ("ABC"), // not multiple of 2 (2)
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
