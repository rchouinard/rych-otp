<?php
/**
 * Ryan's OATH-OTP Library
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2014, Ryan Chouinard
 * @link https://github.com/rchouinard/rych-otp
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP;

/**
 * One-Time Password Base Class
 */
abstract class AbstractOTP implements OTPInterface
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
     * @var Seed
     */
    protected $secret;

    /**
     * @var integer
     */
    protected $window;

    /**
     * {@inheritdoc}
     */
    public function __construct($secret, array $options = array ())
    {
        $this->setSecret($secret);
        $this->processOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($counter = 0)
    {
        $digits = $this->getDigits();
        $hashFunction = $this->getHashFunction();
        $secret = $this->getSecret()->getValue(Seed::FORMAT_RAW);

        $counter = self::counterToString($counter);
        $hash = hash_hmac($hashFunction, $counter, $secret, true);

        $otp = self::truncateHash($hash);
        if ($digits < 10) {
            $otp %= pow(10, $digits);
        }

        return str_pad($otp, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Get the number of digits in the one-time password
     *
     * @return integer Returns the number of digits.
     */
    public function getDigits()
    {
        return $this->digits;
    }

    /**
     * Set the number of digits in the one-time password
     *
     * @param  integer $digits The number of digits.
     * @return self    Returns an instance of self for method chaining.
     * @throws \InvalidArgumentException Thrown if the requested number of
     *                 digits is outside of the inclusive range 1-10.
     */
    public function setDigits($digits)
    {
        $digits = abs(intval($digits));
        if ($digits < 1 || $digits > 10) {
            throw new \InvalidArgumentException('Digits must be a number between 1 and 10 inclusive');
        }
        $this->digits = $digits;

        return $this;
    }

    /**
     * Get the hash function
     *
     * @return string  Returns the hash function.
     */
    public function getHashFunction()
    {
        return $this->hashFunction;
    }

    /**
     * Set the hash function
     *
     * @param  string  $hashFunction The hash function.
     * @return self    Returns an instance of self for method chaining.
     * @throws \InvalidArgumentException Thrown if the supplied hash function
     *                 is not supported.
     */
    public function setHashFunction($hashFunction)
    {
        $hashFunction = strtolower($hashFunction);
        if (!in_array($hashFunction, hash_algos())) {
            throw new \InvalidArgumentException("$hashFunction is not a supported hash function");
        }
        $this->hashFunction = $hashFunction;

        return $this;
    }

    /**
     * Get the shared secret
     *
     * @return Seed    Returns an encoded {@link Seed} instance.
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Set the shared secret
     *
     * @param  Seed|string $secret
     * @return self        Returns an instance of self for method chaining.
     */
    public function setSecret($secret)
    {
        if (!$secret instanceof Seed) {
            $secret = new Seed($secret);
        }
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get the window value
     *
     * @return integer Returns the window value.
     */
    public function getWindow()
    {
        return $this->window;
    }

    /**
     * Set the window value
     *
     * @param  integer $window The window value.
     * @return self    Returns an instance of self for method chaining.
     */
    public function setWindow($window)
    {
        $window = abs(intval($window));
        $this->window = $window;

        return $this;
    }

    /**
     * @param  array   $options
     * @return void
     */
    private function processOptions(array $options)
    {
        // Option names taken from Google Authenticator docs for consistency
        $options = array_merge(array (
                'algorithm' => 'sha1',
                'digits' => 6,
                'window' => 4,
            ), array_change_key_case($options, CASE_LOWER)
        );

        $this->setDigits($options['digits']);
        $this->setHashFunction($options['algorithm']);
        $this->setWindow($options['window']);
    }

    /**
     * Extract 4 bytes from a hash value
     *
     * Uses the method defined in RFC 4226, § 5.4.
     *
     * @static
     * @param  string  $hash Hash value.
     * @return integer Returns the truncated hash value.
     */
    private static function truncateHash($hash)
    {
        $offset = ord($hash[19]) & 0xf;
        $value  = (ord($hash[$offset + 0]) & 0x7f) << 24;
        $value |= (ord($hash[$offset + 1]) & 0xff) << 16;
        $value |= (ord($hash[$offset + 2]) & 0xff) << 8;
        $value |= (ord($hash[$offset + 3]) & 0xff);

        return $value;
    }

    /**
     * Convert an integer counter into a string of 8 bytes
     *
     * @static
     * @param  integer $counter The counter value.
     * @return string  Returns an 8-byte binary string.
     */
    private static function counterToString($counter)
    {
        $temp = '';
        while ($counter != 0) {
            $temp .= chr($counter & 0xff);
            $counter >>= 8;
        }

        return substr(str_pad(strrev($temp), 8, "\0", STR_PAD_LEFT), 0, 8);
    }

}
