<?php

namespace Rych\OTP\Test\Seed;

use PHPUnit_Framework_TestCase as TestCase;
use Rych\OTP\Seed\Encoder\Base32 as Base32Encoder;
use Rych\OTP\Seed\Encoder\Hex as HexEncoder;

class EncoderTest extends TestCase
{

    public function testHexEncoder()
    {
        $vectors = array (
            "\x53\x45\x43\x52\x45\x54\x4b\x45\x59\x73\x65\x63\x72\x65\x74\x6b\x65\x79" => '5345435245544b45597365637265746b6579',
            "\x53\x45\x43\x52\x45\x54\x4b\x45\x59\x31\x73\x65\x63\x72\x65\x74\x6b\x65\x79\x32" => '5345435245544b4559317365637265746b657932',
            "\x4b\x60\x28\x93\xd7\xe6\xb8\xaa\x1e\x7a\x3d\x87\x9c\x26\x30\x63\x6b\x09\xe1\xc4" => '4b602893d7e6b8aa1e7a3d879c2630636b09e1c4',
            "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00" => '0000000000000000000000000000000000000000',
        );

        $encoder = new HexEncoder;
        foreach ($vectors as $raw => $hex) {
            $this->assertEquals($hex, $encoder->encode($raw), "Hex encoder failed to produce expected result $hex.");
            $this->assertEquals($raw, $encoder->decode($hex), "Hex decoder failed to produce expected result from $hex.");
        }
    }

    public function testBase32Encoder()
    {
        $vectors = array (
            "\x53\x45\x43\x52\x45\x54\x4b\x45\x59\x73\x65\x63\x72\x65\x74\x6b\x65\x79" => 'KNCUGUSFKRFUKWLTMVRXEZLUNNSXS===',
            "\x53\x45\x43\x52\x45\x54\x4b\x45\x59\x31\x73\x65\x63\x72\x65\x74\x6b\x65\x79\x32" => 'KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS',
            "\x4b\x60\x28\x93\xd7\xe6\xb8\xaa\x1e\x7a\x3d\x87\x9c\x26\x30\x63\x6b\x09\xe1\xc4" => 'JNQCRE6X424KUHT2HWDZYJRQMNVQTYOE',
            "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00" => 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
        );

        $encoder = new Base32Encoder;
        foreach ($vectors as $raw => $base32) {
            $this->assertEquals($base32, $encoder->encode($raw), "Base32 encoder failed to produce expected result $base32.");
            $this->assertEquals($raw, $encoder->decode($base32), "Base32 decoder failed to produce expected result from $base32.");
        }
    }

}