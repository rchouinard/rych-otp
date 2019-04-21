<?php

namespace Rych\OTP\Encoder;

use Rych\OTP\Encoder\Exception\RuntimeException;

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

    public function invalidDataProvider() : array
    {
        return array (
            // Encoded, Decoded
            array ("ABC"), // not multiple of 4 (1)
            array ("ABCDEF"), // not multiple of 4 (2)
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
