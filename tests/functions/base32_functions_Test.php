<?php
/**
 * This file is part of Rych\OATH-OTP
 *
 * (c) Ryan Chouinard <rchouinard@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rych\Otp\Test;

use Rych\Otp;

class base32_functions_Test extends \PHPUnit_Framework_TestCase
{

    /**
     * Provides base32 test vectors as defined in RFC 4648
     * 
     * @return array
     */
    public function base32VectorProvider()
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

    /**
     * @test
     * @dataProvider base32VectorProvider()
     */
    public function base32_encode($encoded, $decoded)
    {
        $this->assertSame($encoded, Otp\base32_encode($decoded));
    }

    /**
     * @test
     * @dataProvider base32VectorProvider()
     */
    public function base32_decode($encoded, $decoded)
    {
        $this->assertSame($decoded, Otp\base32_decode($encoded));
        $this->assertSame($decoded, Otp\base32_decode("1" . $encoded, false));
        $this->assertFalse(Otp\base32_decode("1" . $encoded, true));
    }

}
