<?php

namespace Rych\OTP\Tests\Encoder;

use Rych\OTP\Encoder\Base32Encoder;
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
        return [
            // Encoded, Decoded
            ["", ""],
            ["MY======", "f"],
            ["MZXQ====", "fo"],
            ["MZXW6===", "foo"],
            ["MZXW6YQ=", "foob"],
            ["MZXW6YTB", "fooba"],
            ["MZXW6YTBOI======", "foobar"],
        ];
    }

    public function invalidDataProvider() : array
    {
        return [
            // Encoded, Decoded
            ["ABCDEFG"], // not multiple of 8 (1)
            ["ABCDEFGHIJKL"], // not multiple of 8 (2)
            ["1nV@liD!"], // invalid characters
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
