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
class HOTP extends AbstractOTP
{
    /**
     * @var integer
     */
    protected $digits;

    /**
     * @var string
     */
    protected $hashFunction;

    /**
     * @var integer
     */
    protected $lastCounterOffset;

    /**
     * @var integer
     */
    protected $window;

    /**
     * {@inheritdoc}
     */
    public function calculate(int $counter = 0) : string
    {
        $digits = $this->getDigits();
        $hashFunction = $this->getHashFunction();
        $secret = $this->getSecret()->getValue(Seed::FORMAT_RAW);

        $counter = $this->counterToString($counter);
        $hash = hash_hmac($hashFunction, $counter, $secret, true);

        $otp = $this->truncateHash($hash);
        if ($digits < 10) {
            $otp %= pow(10, $digits);
        }

        return str_pad((string) $otp, $digits, "0", STR_PAD_LEFT);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(string $otp, int $counter = 0) : bool
    {
        $counter = max(0, $counter);
        $window = $this->getWindow();

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
     * Get the number of digits in the one-time password
     *
     * @return integer Returns the number of digits.
     */
    public function getDigits() : int
    {
        return $this->digits;
    }

    /**
     * Set the number of digits in the one-time password
     *
     * @param  integer $digits The number of digits.
     * @return self    Returns an instance of self for method chaining.
     * @throws InvalidArgumentException Thrown if the requested number of
     *                 digits is outside of the inclusive range 1-10.
     */
    public function setDigits(int $digits) : self
    {
        $digits = abs(intval($digits));
        if ($digits < 1 || $digits > 10) {
            throw new InvalidArgumentException("Digits must be a number between 1 and 10 inclusive");
        }
        $this->digits = $digits;

        return $this;
    }

    /**
     * Get the hash function
     *
     * @return string  Returns the hash function.
     */
    public function getHashFunction() : string
    {
        return $this->hashFunction;
    }

    /**
     * Set the hash function
     *
     * @param  string  $hashFunction The hash function.
     * @return self    Returns an instance of self for method chaining.
     * @throws InvalidArgumentException Thrown if the supplied hash function
     *                 is not supported.
     */
    public function setHashFunction(string $hashFunction) : self
    {
        $hashFunction = strtolower($hashFunction);
        if (!in_array($hashFunction, hash_algos())) {
            throw new InvalidArgumentException("$hashFunction is not a supported hash function");
        }
        $this->hashFunction = $hashFunction;

        return $this;
    }

    /**
     * Get the counter offset value of the last valid counter value
     *
     * Useful to determine how far ahead the client counter is of the server
     * value. Returned value will be between 0 and the configured window value.
     * A return value of null indicates that the last counter verification
     * failed.
     *
     * @return integer|null Returns the offset of the last valid counter value.
     */
    public function getLastValidCounterOffset() : ?int
    {
        return $this->lastCounterOffset;
    }

    /**
     * Get the window value
     *
     * @return integer Returns the window value.
     */
    public function getWindow() : int
    {
        return $this->window;
    }

    /**
     * Set the window value
     *
     * @param  integer $window The window value.
     * @return self    Returns an instance of self for method chaining.
     */
    public function setWindow(int $window) : self
    {
        $window = abs(intval($window));
        $this->window = $window;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function processOptions(array $options) : void
    {
        // Option names taken from Google Authenticator docs for consistency
        $options = array_merge([
            "algorithm" => "sha1",
            "digits" => 6,
            "window" => 4,
        ], array_change_key_case($options, CASE_LOWER));

        $this->setDigits($options["digits"]);
        $this->setHashFunction($options["algorithm"]);
        $this->setWindow($options["window"]);
    }
}
