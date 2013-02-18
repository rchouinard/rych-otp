<?php

namespace Rych\OTP\Test;

use PHPUnit_Framework_TestCase as TestCase;
use Rych\OTP\Seed;

class SeedTest extends TestCase
{

    /**
     * Test that two generated seeds generate different values
     *
     * @test
     * @return void
     */
    public function testGeneratingSeedGeneratesDifferentValues()
    {
        $seed1 = Seed::generate();
        $seed2 = Seed::generate();

        $this->assertFalse((string) $seed1 == (string) $seed2, 'Two seed objects instantiated with generate() method appear to have the same seed value.');
    }

    /**
     * Test that the Seed constructor is able to detect passed-in seed formats
     *
     * @test
     * @return void
     */
    public function testPassingSeedValueToConstructorCorrectlyDetectsValueFormat()
    {
        // Hex
        $seed = new Seed('5345435245544b4559317365637265746b657932');
        $this->assertEquals('SECRETKEY1secretkey2', $seed->getValue(Seed::FORMAT_RAW), 'Hex seed value passed to constructor failed to decode to expected raw value.');

        // Base32
        $seed = new Seed('KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS');
        $this->assertEquals('SECRETKEY1secretkey2', $seed->getValue(Seed::FORMAT_RAW), 'Base32 seed value passed to constructor failed to decode to expected raw value.');

        // Raw
        $seed = new Seed("\x53\x45\x43\x52\x45\x54\x4b\x45\x59\x31\x73\x65\x63\x72\x65\x74\x6b\x65\x79\x32");
        $this->assertEquals('SECRETKEY1secretkey2', $seed->getValue(Seed::FORMAT_RAW), 'Raw seed value passed to constructor failed to decode to expected raw value.');
    }

    /**
     * Test that the global default format setting affects output methods
     *
     * @test
     * @return void
     */
    public function testSetFormatMethodProperlyControlsDefaultOutputFormat()
    {
        $seed = new Seed('SECRETKEY1secretkey2');

        $seed->setFormat(Seed::FORMAT_HEX);
        $this->assertEquals('5345435245544b4559317365637265746b657932', $seed, 'Seed object failed to return expected hex value using __toString() method after calling setFormat() method.');
        $this->assertEquals('5345435245544b4559317365637265746b657932', $seed->getValue(), 'Seed object failed to return expected hex value using getValue() method after calling setFormat() method.');

        $seed->setFormat(Seed::FORMAT_BASE32);
        $this->assertEquals('KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS', $seed, 'Seed object failed to return expected base32 value using __toString() method after calling setFormat() method.');
        $this->assertEquals('KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS', $seed->getValue(), 'Seed object failed to return expected base32 value using getValue() method after calling setFormat() method.');

        $seed->setFormat(Seed::FORMAT_RAW);
        $this->assertEquals('SECRETKEY1secretkey2', $seed, 'Seed object failed to return expected raw value using __toString() method after calling setFormat() method.');
        $this->assertEquals('SECRETKEY1secretkey2', $seed->getValue(), 'Seed object failed to return expected raw value using getValue() method after calling setFormat() method.');
    }

    /**
     * Test that requesting a specific format does not affet global default
     *
     * @test
     * @return void
     */
    public function testGetValueMethodDoesNotChangeDefaultFormat()
    {
        $seed = new Seed('SECRETKEY1secretkey2');
        $seed->setFormat(Seed::FORMAT_HEX);

        $this->assertEquals('KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS', $seed->getValue(Seed::FORMAT_BASE32), 'Seed object failed to return expected base32 value using getValue() method with format argument.');
        $this->assertEquals('SECRETKEY1secretkey2', $seed->getValue(Seed::FORMAT_RAW), 'Seed object failed to return expected raw value using getValue() method with format argument.');

        $this->assertEquals('5345435245544b4559317365637265746b657932', $seed, 'Seed object failed to return expected hex value using __toString() method.');
        $this->assertEquals('5345435245544b4559317365637265746b657932', $seed->getValue(), 'Seed object failed to return expected hex value using getValue() method without format argument.');
    }

}
