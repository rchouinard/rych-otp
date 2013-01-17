<?php

namespace Rych\OTP\Test;

use PHPUnit_Framework_TestCase as TestCase;
use Rych\OTP\HOTP;

class HOTPTest extends TestCase
{

    public function testCalculateMethodReturnsValidValuesForKnownInput()
    {
        $hotp = new HOTP;
        $hotp->setDigits(6);

        // Check a large range of counter values
        // Cap at 32-bit PHP_INT_MAX for now
        $this->assertEquals('268911', $hotp->calculate('5345435245544b4559317365637265746b657932', 0));
        $this->assertEquals('473411', $hotp->calculate('5345435245544b4559317365637265746b657932', 0x40000000));
        $this->assertEquals('109402', $hotp->calculate('5345435245544b4559317365637265746b657932', 0x7FFFFFFF));

        // Same as above, with 8 digits
        $hotp->setDigits(8);
        $this->assertEquals('14268911', $hotp->calculate('5345435245544b4559317365637265746b657932', 0));
        $this->assertEquals('90473411', $hotp->calculate('5345435245544b4559317365637265746b657932', 0x40000000));
        $this->assertEquals('26109402', $hotp->calculate('5345435245544b4559317365637265746b657932', 0x7FFFFFFF));

        // Also checks that the seed formats are detected correctly
        $hotp->setDigits(6);
        $this->assertEquals('046095', $hotp->calculate('5345435245544b4559317365637265746b657932', 42), 'HOTP object failed to produce expected value given known hex seed.');
        $this->assertEquals('046095', $hotp->calculate('KNCUGUSFKRFUKWJRONSWG4TFORVWK6JS', 42), 'HOTP object failed to produce expected value given known base32 seed.');
        $this->assertEquals('046095', $hotp->calculate('SECRETKEY1secretkey2', 42), 'HOTP object failed to produce expected value given known raw seed.');
    }

    public function testValidateMethodConfirmsKnownValues()
    {
        $hotp = new HOTP;
        $hotp->setDigits(6);
        $hotp->setWindow(4);

        // Completely wrong guess
        $this->assertFalse($hotp->validate('5345435245544b4559317365637265746b657932', '123456', 0));

        // Given OTP is ahead of our counter, but within the window
        $this->assertEquals(104, $hotp->validate('5345435245544b4559317365637265746b657932', '150463', 100));

        // Given OTP is behind our counter (by one, in this case)
        $this->assertFalse($hotp->validate('5345435245544b4559317365637265746b657932', '150463', 105));

        // Given OTP is ahead of our counter, but outside the window (by one)
        $this->assertFalse($hotp->validate('5345435245544b4559317365637265746b657932', '069682', 100));

        // Make sure we can't validate when digits are wrong
        $this->assertSame(0, $hotp->validate('5345435245544b4559317365637265746b657932', '268911', 0));
        $this->assertSame(false, $hotp->validate('5345435245544b4559317365637265746b657932', '14268911', 0));
        $hotp->setDigits(8);
        $this->assertSame(false, $hotp->validate('5345435245544b4559317365637265746b657932', '268911', 0));
        $this->assertSame(0, $hotp->validate('5345435245544b4559317365637265746b657932', '14268911', 0));

    }

}
