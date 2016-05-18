<?php
/**
 * This file is part of Rych\OATH-OTP
 *
 * (c) Ryan Chouinard <rchouinard@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rych\Otp;

class Secret
{
    const FORMAT_RAW = "raw";
    const FORMAT_HEX = "hex";
    const FORMAT_BASE32 = "base32";

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $value;

    /**
     * @param   string  $value
     * @param   string  $format
     */
    public function __construct($value, $format = null)
    {
        if (!$format) {
            if (preg_match("/^[0-9a-f]+$/i", $value)) {
                $format = self::FORMAT_HEX;
            } elseif (preg_match("/^[2-7a-z=]+$/i", $value)) {
                $format = self::FORMAT_BASE32;
            } else {
                $format = self::FORMAT_RAW;
            }
        }

        $this->format = $format;
        $this->value = (string) $value;
    }

    /**
     * @return  string
     */
    public function getValue()
    {
        $value = $this->value;
        if ($this->format !== self::FORMAT_RAW) {
            if ($this->format === self::FORMAT_HEX) {
                $value = hex2bin($value);
            } elseif ($this->format === self::FORMAT_BASE32) {
                $value = base32_decode($value);
            }
        }

        return $value;
    }

    /**
     * @return  string
     * @codeCoverageIgnore
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * @param   string  $algo
     * @return  Secret
     */
    public static function generate($algo = TOTP_SHA1)
    {
        return new Secret(generate_secret($algo), self::FORMAT_RAW);
    }
}
