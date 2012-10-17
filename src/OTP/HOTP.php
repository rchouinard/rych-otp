<?php

namespace OTP;

use OTP\Base32;

require_once 'OTP/Base32.php';

class HOTP
{

    private $seed;

    /**
     * Generate a base32 encoded seed value
     *
     * This method will also set the seed value for future operation to the
     * generated value.
     *
     * @param integer $bytes Number of bytes to use. Default of 10 produces an
     *     80-bit seed value.
     * @return string The base32 encoded seed value
     */
    public function generateSeed($bytes = 10)
    {
        $count = (int) $bytes;
        $output = openssl_random_pseudo_bytes($count);

        $b32 = new Base32;
        return $this->seed = $b32->encode($output);
    }

    /**
     * @param string $seed The base32 encoded seed value
     * @return self
     */
    public function setSeed($seed)
    {
        $this->seed = $seed;
        return $this;
    }

    /**
     * @return string The base32 encoded seed value
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * Generate a one-time password based on the current seed and given counter
     *
     * @param string $counter
     * @return integer
     */
    public function generateOTP($counter, $length = 6)
    {
        $b32 = new Base32;

        // Counter must be a 64-bit integer
        $counter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $counter, $b32->decode($this->getSeed()), true);

        return $this->truncate($hash) % pow(10, $length);
    }

    /**
     * Extract 4 bytes from the hash value, used to calculate the OTP digits
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

}
