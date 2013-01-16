<?php
/**
 * RFC 4226 OTP Library
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP;

use Rych\OTP\Seed\Encoder\Base32;

/**
 * HMAC-Based One-Time Passwords
 *
 * @package Rych\OTP
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
    private function genRandomBytes($length)
    {
        $length = (int) $length;
        $output = '';

        // Windows platforms
        if ((PHP_OS & "\xDF\xDF\xDF") === 'WIN') {
            // Try MCrypt first
            if (function_exists('mcrypt_create_iv')) {
                $output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);

            // On Windows, PHP versions < 5.3.4 have a potential blocking
            // condition with openssl_random_pseudo_bytes(). Versions >= 5.4.3
            // work exactly like mcrypt_create_iv() internally.
            } else if (function_exists('openssl_random_pseudo_bytes') && version_compare(PHP_VERSION, '5.3.4', '>=')) {
                $output = openssl_random_pseudo_bytes($length);
            }
        // Non-Windows platforms
        } else {
            // Try OpenSSL first
            if (function_exists('openssl_random_pseudo_bytes')) {
                $output = openssl_random_pseudo_bytes($length);
            // Attempt to read straight from /dev/urandom
            } else if (is_readable('/dev/urandom') && $fp = @fopen('/dev/urandom', 'rb')) {
                if (function_exists('stream_set_read_buffer')) {
                    stream_set_read_buffer($fp, 0);
                }
                $output = fread($fp, $length);
                fclose($fp);
            // Try mcrypt_create_iv() - basically reads from /dev/urandom, but
            // slower and without being limited by open_basedir.
            } else if (function_exists('mcrypt_create_iv')) {
                $output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            }
        }

        // https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP
        // We don't read from /dev/urandom here, as we try that above.
        // If that had worked, we wouldn't be here now.
        if (strlen($output) != $length) {
            $output = '';
            $bitsPerRound = 2; // bits of entropy collected in each clock drift round
            $msecPerRound = 400; // expected running time of each round in microseconds
            $hashLength = 20; // SHA-1 Hash length
            $total = $length; // total bytes of entropy to collect

            do {
                $bytes = ($total > $hashLength) ? $hashLength : $total;
                $total -= $bytes;

                $entropy = rand() . uniqid(mt_rand(), true);
                $entropy .= implode('', @fstat(@fopen(__FILE__, 'r')));
                $entropy .= memory_get_usage();

                for ($i = 0; $i < 3; ++$i) {
                    $counter1 = microtime(true);
                    $var = sha1(mt_rand());
                    for ($j = 0; $j < 50; ++$j) {
                        $var = sha1($var);
                    }
                    $counter2 = microtime(true);
                    $entropy .= $counter1 . $counter2;
                }

                $rounds = (int) ($msecPerRound * 50 / (int) (($counter2 - $counter1) * 1000000));
                $iterations = $bytes * (int) (ceil(8 / $bitsPerRound));

                for ($i = 0; $i < $iterations; ++$i) {
                    $counter1 = microtime();
                    $var = sha1(mt_rand());
                    for ($j = 0; $j < $rounds; ++$j) {
                        $var = sha1($var);
                    }
                    $counter2 = microtime();
                    $entropy .= $counter1 . $counter2;
                }

                $output .= sha1($entropy, true);
            } while ($length > strlen($output));
        }

        return substr($output, 0, $length);
    }

}
