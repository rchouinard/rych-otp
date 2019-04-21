<?php
/**
 * Ryan's OATH-OTP Library
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2019, Ryan Chouinard
 * @link https://github.com/rchouinard/rych-otp
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP;

/**
 * One-Time Password Seed/Key Tests
 */
class SeedTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Test that the get/set format methods work as expected
     *
     * @test
     * @return void
     */
    public function testGetFormatAndSetFormatMethodsBehaveAsExpected()
    {
        $seed = new Seed();

        $seed->setFormat(Seed::FORMAT_BASE32);
        $this->assertEquals(Seed::FORMAT_BASE32, $seed->getFormat());

        $seed->setFormat(Seed::FORMAT_HEX);
        $this->assertEquals(Seed::FORMAT_HEX, $seed->getFormat());

        $seed->setFormat(Seed::FORMAT_RAW);
        $this->assertEquals(Seed::FORMAT_RAW, $seed->getFormat());
    }

    /**
     * Test that the global default format setting affects output methods
     *
     * @test
     * @return void
     */
    public function testSetFormatMethodProperlyControlsDefaultOutputFormat()
    {
        $seed = new Seed();
        $seed->setValue("SECRETKEY1secretkey2", Seed::FORMAT_RAW);

        $seed->setFormat(Seed::FORMAT_BASE32);
        $this->assertEquals("KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS", $seed);
        $this->assertEquals("KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS", $seed->getValue());

        $seed->setFormat(Seed::FORMAT_HEX);
        $this->assertEquals("5345435245544b4559317365637265746b657932", $seed);
        $this->assertEquals("5345435245544b4559317365637265746b657932", $seed->getValue());

        $seed->setFormat(Seed::FORMAT_RAW);
        $this->assertEquals("SECRETKEY1secretkey2", $seed);
        $this->assertEquals("SECRETKEY1secretkey2", $seed->getValue());
    }

    /**
     * Test that the set value method respects the format parameter
     *
     * @test
     * @return void
     */
    public function testSetValueRespectsFormatParameter()
    {
        $seed = new Seed();

        $seed->setValue("KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS", Seed::FORMAT_BASE32);
        $this->assertEquals("SECRETKEY1secretkey2", $seed->getValue(Seed::FORMAT_RAW));

        $seed->setValue("5345435245544b4559317365637265746b657932", Seed::FORMAT_HEX);
        $this->assertEquals("SECRETKEY1secretkey2", $seed->getValue(Seed::FORMAT_RAW));

        $seed->setValue("SECRETKEY1secretkey2", Seed::FORMAT_RAW);
        $this->assertEquals("SECRETKEY1secretkey2", $seed->getValue(Seed::FORMAT_RAW));
    }

    /**
     * Test that the get value method respects the format parameter
     *
     * @test
     * @return void
     */
    public function testGetValueRespectsFormatParameter()
    {
        $seed = new Seed();
        $seed->setValue("SECRETKEY1secretkey2", Seed::FORMAT_RAW);

        $this->assertEquals("KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS", $seed->getValue(Seed::FORMAT_BASE32));
        $this->assertEquals("5345435245544b4559317365637265746b657932", $seed->getValue(Seed::FORMAT_HEX));
        $this->assertEquals("SECRETKEY1secretkey2", $seed->getValue(Seed::FORMAT_RAW));
    }

    /**
     * Test that static generate method returns configured Seed
     *
     * @test
     * @return void
     */
    public function testGenerateMethodReturnsValidSeed()
    {
        $seed = Seed::generate(8);

        $this->assertInstanceOf(Seed::class, $seed);
        $this->assertEquals(8, strlen($seed->getValue(Seed::FORMAT_RAW)));
    }

    /**
     * Test that static generate method returns random Seed
     *
     * @test
     * @return void
     */
    public function testGenerateMethodProducesRandomSeed()
    {
        $seed1 = Seed::generate(8);
        $seed2 = Seed::generate(8);

        // Randomness is near impossible to teset for, so just make sure
        // two generated instances don't contain the same value
        $this->assertFalse($seed1->getValue() == $seed2->getValue());
    }

    /**
     * Test that the Seed constructor is able to detect passed-in seed formats
     *
     * @test
     * @return void
     */
    public function testPassingSeedValueToConstructorCorrectlyDetectsValueFormat()
    {
        // Base32
        $seed = new Seed("KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS");
        $this->assertEquals("SECRETKEY1secretkey2", $seed->getValue(Seed::FORMAT_RAW));

        // Hex
        $seed = new Seed("5345435245544b4559317365637265746b657932");
        $this->assertEquals("SECRETKEY1secretkey2", $seed->getValue(Seed::FORMAT_RAW));

        // Raw
        $seed = new Seed("\x53\x45\x43\x52\x45\x54\x4b\x45\x59\x31\x73\x65\x63\x72\x65\x74\x6b\x65\x79\x32");
        $this->assertEquals("SECRETKEY1secretkey2", $seed->getValue(Seed::FORMAT_RAW));
    }

}
