<?php
/**
 * RFC 4226 OTP Library
 *
 * @package OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace OTP;

use OTP\Base32;

require_once 'OTP/Base32.php';

/**
 * HMAC-Based One-Time Passwords
 *
 * @package OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class HOTP
{

    /**
     * @var Base32
     */
    private $base32;

    /**
     * @var string
     */
    private $seed;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->base32 = new Base32;
    }

    /**
     * Generate a base32 encoded seed value.
     *
     * This method should only be called on the first-time setup of a new client
     * token, or if the client token must be reseeded. Subsequent use of this
     * class for a given client should use the setSeed() method to provide the
     * previously generated stored seed value.
     *
     * Will also set the seed value for future operation to the generated value.
     *
     * @param integer $bytes Number of bytes to use. Default of 20 produces an
     *     160-bit seed value as recommended by RFC 4226 Section 4 R6.
     * @return string The base32 encoded seed value.
     */
    public function generateSeed($bytes = 20)
    {
        $bytes = (int) $bytes;
        $output = $this->genRandomBytes($bytes);

        return $this->seed = $this->base32->encode($output);
    }

    /**
     * Set the seed value to use for future operations.
     *
     * @param string $seed The base32 encoded seed value
     * @return OTP\HOTP Returns an instance of self for method chaining.
     */
    public function setSeed($seed)
    {
        $this->seed = $seed;
        return $this;
    }

    /**
     * Get the current seed value.
     *
     * @return string The base32 encoded seed value.
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * Generate an OTP based on the current seed and given counter.
     *
     * @param integer $counter The counter value to use for the OTP.
     * @param integer $length Desired length of the OTP. Defaults to 6.
     * @return integer Returns the generated OTP.
     */
    public function generateOTP($counter, $length = 6)
    {
        // Length must be at least 6, according to RFC 4226 Section 4 R4.
        $length = max((int) $length, 6);

        // Counter must be a 64-bit integer, so we fake it.
        $counter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $counter, $this->base32->decode($this->getSeed()), true);

        return $this->truncate($hash) % pow(10, $length);
    }

    /**
     * Verify an OTP using the specified counter and an optional window.
     *
     * @param integer $otp The user-supplied OTP value.
     * @param integer $counter The counter value to base the OTP on.
     * @param integer $window How many times the counter should be adjusted for
     *     error. Defaults to 4.
     * @return integer Returns the counter value which matched on success, or
     *     false on failure.
     */
    public function verifyOTP($otp, $counter, $window = 4)
    {
        $valid = false;
        $counter = (int) $counter;

        for ($current = $counter; $current <= $counter + (int) $window; ++$current) {
            if ($otp == $this->generateOTP($current, strlen($otp))) {
                $valid = $current;
                break;
            }
        }

        return $valid;
    }

    /**
     * Extract 4 bytes from the hash value, used to calculate the OTP digits.
     *
     * Uses the method defined in RFC 4226, Section 5.3.
     *
     * @param string $hash HMAC-SHA1 hash value of the counter using the binary
     *     seed as the key.
     * @param integer $length Number of digits to produce for the OTP.
     * @return integer Truncated hash value.
     */
    private function truncate($hash)
    {
        $offset = ord($hash[19]) & 0xf;
        $value  = (ord($hash[$offset + 0]) & 0x7f) << 24;
        $value |= (ord($hash[$offset + 1]) & 0xff) << 16;
        $value |= (ord($hash[$offset + 2]) & 0xff) << 8;
        $value |= (ord($hash[$offset + 3]) & 0xff);

        return $value;
    }

    /**
     * Generate a random byte string of the requested length.
     *
     * @param integer $count Number of bytes to generate.
     * @return string Random byte string.
     */
    private function genRandomBytes($count)
    {
        $count = (int) $count;

        // Try OpenSSL's random generator
        $output = '';
        if (function_exists('openssl_random_pseudo_bytes')) {
            $strongCrypto = false;
            // NOTE: The $strongCrypto argument here isn't telling OpenSSL to
            // generate (or not) cryptographically secure data. It's passed
            // by reference, and will be set to true or false after the
            // function call to indicate whether or not OpenSSL is confident
            // that the generated data can be used for cryptographic operations.
            $output = openssl_random_pseudo_bytes($count, $strongCrypto);
            if ($strongCrypto && strlen($output) == $count) {
                return $output;
            }
        }

        // Try creating an mcrypt IV
        $output = '';
        if (function_exists('mcrypt_create_iv')) {
            $output = mcrypt_create_iv($count, MCRYPT_DEV_URANDOM);
            if (strlen($output) == $count) {
                return $output;
            }
        }

        // Try reading from /dev/urandom, if present
        $output = '';
        if (is_readable('/dev/urandom') && ($fh = fopen('/dev/urandom', 'rb'))) {
            $output = fread($fh, $count);
            fclose($fh);
            if (strlen($output) == $count) {
                return $output;
            }
        }

        // Fall back to a locally generated "random" string as last resort
        $randomState = microtime();
        if (function_exists('getmypid')) {
            $randomState .= getmypid();
        }
        $output = '';
        for ($i = 0; $i < $count; $i += 16) {
            $randomState = md5(microtime() . $randomState);
            $output .= md5($randomState, true);
        }
        $output = substr($output, 0, $count);

        return $output;
    }

}
