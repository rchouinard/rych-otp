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

class otp_functions_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * A 20-byte secret for SHA1 OTPs
     */
    const SECRET_SHA1 = "12345678901234567890";

    /**
     * A 32-byte secret for SHA256 OTPs
     */
    const SECRET_SHA256 = "12345678901234567890123456789012";

    /**
     * A 64-byte secret for SHA512 OTPs
     */
    const SECRET_SHA512 = "1234567890123456789012345678901234567890123456789012345678901234";

    /**
     * Provides HOTP test vectors as defined in RFC 4226
     *
     * @return array
     */
    public function hotpVectorProvider()
    {
        $vectors = [
            [0, "755224"],
            [1, "287082"],
            [2, "359152"],
            [3, "969429"],
            [4, "338314"],
            [5, "254676"],
            [6, "287922"],
            [7, "162583"],
            [8, "399871"],
            [9, "520489"],
        ];

        return $vectors;
    }

    /**
     * Provides TOTP test vectors as defined in RFC 6238
     *
     * @return array
     */
    public function totpVectorProvider()
    {
        $vectors =  [
            [strtotime("1970-01-01 00:00:59 UTC"), "94287082", Otp\TOTP_SHA1],
            [strtotime("1970-01-01 00:00:59 UTC"), "46119246", Otp\TOTP_SHA256],
            [strtotime("1970-01-01 00:00:59 UTC"), "90693936", Otp\TOTP_SHA512],
            [strtotime("2005-03-18 01:58:29 UTC"), "07081804", Otp\TOTP_SHA1],
            [strtotime("2005-03-18 01:58:29 UTC"), "68084774", Otp\TOTP_SHA256],
            [strtotime("2005-03-18 01:58:29 UTC"), "25091201", Otp\TOTP_SHA512],
            [strtotime("2005-03-18 01:58:31 UTC"), "14050471", Otp\TOTP_SHA1],
            [strtotime("2005-03-18 01:58:31 UTC"), "67062674", Otp\TOTP_SHA256],
            [strtotime("2005-03-18 01:58:31 UTC"), "99943326", Otp\TOTP_SHA512],
            [strtotime("2009-02-13 23:31:30 UTC"), "89005924", Otp\TOTP_SHA1],
            [strtotime("2009-02-13 23:31:30 UTC"), "91819424", Otp\TOTP_SHA256],
            [strtotime("2009-02-13 23:31:30 UTC"), "93441116", Otp\TOTP_SHA512],
            [strtotime("2033-05-18 03:33:20 UTC"), "69279037", Otp\TOTP_SHA1],
            [strtotime("2033-05-18 03:33:20 UTC"), "90698825", Otp\TOTP_SHA256],
            [strtotime("2033-05-18 03:33:20 UTC"), "38618901", Otp\TOTP_SHA512],
        ];

        // 64-bit systems only
        if (\PHP_INT_SIZE === 8) {
            array_push($vectors,
                [strtotime("2603-10-11 11:33:20 UTC"), "65353130", Otp\TOTP_SHA1],
                [strtotime("2603-10-11 11:33:20 UTC"), "77737706", Otp\TOTP_SHA256],
                [strtotime("2603-10-11 11:33:20 UTC"), "47863826", Otp\TOTP_SHA512]
            );
        }

        return $vectors;
    }

    /**
     * @test
     * @dataProvider hotpVectorProvider()
     */
    public function hotp_generate($counter, $otp)
    {
        $secret = self::SECRET_SHA1;

        $this->assertEquals($otp, Otp\hotp_generate($secret, $counter, 6));
    }

    /**
     * @test
     * @dataProvider hotpVectorProvider()
     */
    public function hotp_validate($counter, $otp)
    {
        $secret = self::SECRET_SHA1;

        $this->assertSame(0, Otp\hotp_validate($secret, $counter, $otp, 0));

        if ($counter > 0) { // We can't reduce the counter if it's already zero
            $this->assertSame(1, Otp\hotp_validate($secret, $counter - 1, $otp, 1));
            $this->assertFalse(Otp\hotp_validate($secret, $counter - 1, $otp, 0));
        }
    }

    /**
     * @test
     * @dataProvider totpVectorProvider()
     */
    public function totp_generate($now, $otp, $algo)
    {
        $secret = self::SECRET_SHA1;
        if ($algo === Otp\TOTP_SHA256) {
            $secret = self::SECRET_SHA256;
        } elseif ($algo === Otp\TOTP_SHA512) {
            $secret = self::SECRET_SHA512;
        }

        $this->assertEquals($otp, Otp\totp_generate($secret, $now, 8, $algo, 30));
    }

    /**
     * @test
     * @dataProvider totpVectorProvider()
     */
    public function totp_validate($now, $otp, $algo)
    {
        $secret = self::SECRET_SHA1;
        if ($algo === Otp\TOTP_SHA256) {
            $secret = self::SECRET_SHA256;
        } elseif ($algo === Otp\TOTP_SHA512) {
            $secret = self::SECRET_SHA512;
        }

        $this->assertSame(0, Otp\totp_validate($secret, $now, $otp, 0, $algo, 30));
        $this->assertSame(1, Otp\totp_validate($secret, $now - 30, $otp, 1, $algo, 30));
        $this->assertFalse(Otp\totp_validate($secret, $now - 30, $otp, 0, $algo, 30));
    }
}
