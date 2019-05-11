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
    /** @var integer */
    protected $digits;

    /** @var string */
    protected $hashFunction;

    /** @var integer */
    protected $lastCounterOffset;

    /** @var integer */
    protected $window;

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
        $this->lastCounterOffset = $offset;

        return $valid;
    }

    /**
     * Get the counter offset value of the last valid counter value
     *
     * Useful to determine how far ahead the client counter is of the server
     * value. Returned value will be between 0 and the configured window value.
     * A return value of null indicates that the last counter verification
     * failed.
     *
     * @return  integer|null    Returns the offset of the last valid counter value.
     */
    public function getLastValidCounterOffset() : ?int
    {
        return $this->lastCounterOffset;
    }
}
