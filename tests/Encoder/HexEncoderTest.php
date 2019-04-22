<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

namespace Rych\OTP\Tests\Encoder;

use Rych\OTP\Encoder\Exception\RuntimeException;
use Rych\OTP\Encoder\HexEncoder;

class HexEncoderTest extends \PHPUnit\Framework\TestCase
{
    private $encoder;

    protected function setUp() : void
    {
        $this->encoder = new HexEncoder();
    }

    public function vectorProvider() : array
    {
        return [
            // Encoded, Decoded
            ["", ""],
            ["66", "f"],
            ["666f", "fo"],
            ["666f6f", "foo"],
            ["666f6f62", "foob"],
            ["666f6f6261", "fooba"],
            ["666f6f626172", "foobar"],
        ];
    }

    public function invalidDataProvider() : array
    {
        return [
            // Encoded, Decoded
            ["A"], // not multiple of 2 (1)
            ["ABC"], // not multiple of 2 (2)
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
