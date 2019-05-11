<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

namespace Rych\OTP;

use Rych\Otp\Exception\InvalidArgumentException;

/**
 * RFC-4226 HMAC-Based One-Time Password Class
 */
class HOTP extends AbstractOTP implements OTPInterface
{
    /**
     * @inheritdoc
     */
    public function calculate(int $counter = 0) : string
    {
        $digits = $this->options["digits"];
        $hashFunction = $this->options["hash_func"];
        $secret = $this->secret;

        $counter = $this->counterToString($counter);
        $hash = hash_hmac($hashFunction, $counter, $secret, true);

        $otp = $this->truncateHash($hash);
        if ($digits < 10) {
            $otp %= pow(10, $digits);
        }

        return str_pad((string) $otp, $digits, "0", STR_PAD_LEFT);
    }

    /**
     * @inheritdoc
     */
    public function verify(string $otp, int $counter = 0) : bool
    {
        $counter = max(0, $counter);
        $window = $this->options["window"];

        $valid = false;
        $offset = null;
        for ($current = $counter; $current <= $counter + $window; ++$current) {
            if ($otp === $this->calculate($current)) {
                $valid = true;
                $offset = $current - $counter;
                break;
            }
        }
        $this->lastOffset = $offset;

        return $valid;
    }
}
