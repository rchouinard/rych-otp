<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

namespace Rych\OTP;

/**
 * RFC-4226 HMAC-Based One-Time Password Class
 */
class HOTP extends AbstractOTP
{
    /**
     * @inheritdoc OTPInterface::calculate()
     */
    public function calculate(int $counter) : string
    {
        $hash = hash_hmac($this->options["hash_func"], $this->counterToString($counter), $this->secret, true);

        $digits = $this->options["digits"];

        $otp = $this->truncateHash($hash);
        if ($digits < 10) {
            $otp %= 10 ** $digits;
        }

        return str_pad((string) $otp, $digits, "0", STR_PAD_LEFT);
    }

    /**
     * @inheritdoc OTPInterface::verify()
     */
    public function verify(string $otp, int $counter = 0) : bool
    {
        $startCounter = max($counter - $this->options["window"], 0);
        $endCounter = $counter + $this->options["window"];

        $valid = false;
        $offset = null;

        for ($current = $startCounter; $current <= $endCounter; ++$current) {
            // Use of self here forces HOTP's calculate method in child classes
            if ($otp === self::calculate($current)) {
                $valid = true;
                $offset = $counter - $current;

                break;
            }
        }

        $this->lastOffset = $offset;

        return $valid;
    }
}
