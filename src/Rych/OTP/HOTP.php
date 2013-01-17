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

use Rych\OTP\Seed;

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
     * @var integer
     */
    private $digits = 6;

    /**
     * @var integer
     */
    private $window = 0;

    /**
     * Class constructor.
     *
     * @param array $options
     * @return void
     */
    public function __construct(array $options = array ())
    {
        foreach (array_change_key_case($options, CASE_LOWER) as $option => $value) {
            switch ($option) {
                case 'digits':
                    $this->setDigits($value);
                    break;
                case 'window':
                    $this->setWindow($value);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @return integer
     */
    public function getDigits()
    {
        return $this->digits;
    }

    /**
     * @param integer $value
     * @return self
     */
    public function setDigits($value)
    {
        $this->digits = (int) max(min($value, 8), 6);
        return $this;
    }

    /**
     * @return integer
     */
    public function getWindow()
    {
        return $this->window;
    }

    /**
     * @param integer $value
     * @return self
     */
    public function setWindow($value)
    {
        $this->window = (int) max(min($value, PHP_INT_MAX), 0);
        return $this;
    }

    /**
     * Generate an OTP.
     *
     * @param Seed|string $seed The seed value.
     * @param integer $counter The counter value. Defaults to 0.
     * @return integer Returns the generated OTP.
     */
    public function calculate($seed, $counter = 0)
    {
        if (!$seed instanceof Seed) {
            $seed = new Seed($seed);
        }

        // Counter must be a 64-bit integer, so we fake it.
        $counter = pack('N*', 0) . pack('N*', (int) $counter);
        $hash = hash_hmac('sha1', $counter, $seed->getValue(Seed::FORMAT_RAW), true);

        return $this->truncate($hash) % pow(10, $this->digits);
    }

    /**
     * Validate an OTP.
     *
     * @param Seed|string $seed The seed value.
     * @param string $otp The OTP value.
     * @param integer $counter The counter value. Defaults to 0.
     * @return integer Returns the counter value which matched on success, or
     *     false on failure.
     */
    public function validate($seed, $otp, $counter = 0)
    {
        $valid = false;
        $counter = (int) $counter;

        for ($current = $counter; $current <= $counter + $this->window; ++$current) {
            if ($otp == $this->calculate($seed, $current)) {
                $valid = $current;
                break;
            }
        }

        return $valid;
    }

    /**
     * Extract 4 bytes from a hash value.
     *
     * Uses the method defined in RFC 4226, Section 5.3.
     *
     * @param string $hash HMAC-SHA1 hash value of the counter using the raw
     *     seed as the key.
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
