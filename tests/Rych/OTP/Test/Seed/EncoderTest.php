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
            'U0VDUkVUS0VZc2VjcmV0a2V5' => '5345435245544b45597365637265746b6579',
            'U0VDUkVUS0VZMXNlY3JldGtleTI=' => '5345435245544b4559317365637265746b657932',
            'S2Aok9fmuKoeej2HnCYwY2sJ4cQ=' => '4b602893d7e6b8aa1e7a3d879c2630636b09e1c4',
            'AAAAAAAAAAAAAAAAAAAAAAAAAAA=' => '0000000000000000000000000000000000000000',
        );

        $encoder = new HexEncoder;
        foreach ($vectors as $base64 => $hex) {
            $this->assertEquals($hex, $encoder->encode(base64_decode($base64)));
            $this->assertEquals(base64_decode($base64), $encoder->decode($hex));
        }
    }

    public function testBase32Encoder()
    {
        $vectors = array (
            'U0VDUkVUS0VZc2VjcmV0a2V5' => 'KNCUGUSFKRFUKWLTMVRXEZLUNNSXS===',
            'U0VDUkVUS0VZMXNlY3JldGtleTI=' => 'KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS',
            'S2Aok9fmuKoeej2HnCYwY2sJ4cQ=' => 'JNQCRE6X424KUHT2HWDZYJRQMNVQTYOE',
            'AAAAAAAAAAAAAAAAAAAAAAAAAAA=' => 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
        );

        $encoder = new Base32Encoder;
        foreach ($vectors as $base64 => $base32) {
            $this->assertEquals($base32, $encoder->encode(base64_decode($base64)));
            $this->assertEquals(base64_decode($base64), $encoder->decode($base32));
        }
    }

}