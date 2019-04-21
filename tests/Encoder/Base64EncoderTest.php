<?php

namespace Rych\OTP\Tests\Encoder;

use Rych\OTP\Encoder\Base64Encoder;
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
        return [
            // Encoded, Decoded
            ["", ""],
            ["Zg==", "f"],
            ["Zm8=", "fo"],
            ["Zm9v", "foo"],
            ["Zm9vYg==", "foob"],
            ["Zm9vYmE=", "fooba"],
            ["Zm9vYmFy", "foobar"],
        ];
    }

    public function invalidDataProvider() : array
    {
        return [
            // Encoded, Decoded
            ["ABC"], // not multiple of 4 (1)
            ["ABCDEF"], // not multiple of 4 (2)
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
