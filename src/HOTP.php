<?php
/**
 * Ryan's OATH-OTP Library
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2014, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP;

/**
 * RFC-4226 HMAC-Based One-Time Passwords
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2014, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class HOTP extends AbstractOTP
{

    /**
     * @var integer
     */
    protected $lastValidCounterOffset;

    /**
     * Get the counter offset value of the last valid counter value
     *
     * Useful to determine how far ahead the client counter is of the server
     * value. Returned value will be between 0 and the configured window value.
     * A return value of null indicates that the last counter verification
     * failed.
     *
     * @return integer Returns the offset of the last valid counter value.
     */
    public function getLastValidCounterOffset()
    {
        return $this->lastValidCounterOffset;
    }

    /**
     * Validate an OTP
     *
     * @param  string  $otp     The OTP value.
     * @param  integer $counter The counter value. Defaults to 0.
     * @return boolean Returns true if the supplied counter value is valid
     *     within the configured counter window, false otherwise.
     */
    public function validate($otp, $counter = 0)
    {
        $window = $this->getWindow();

        $valid = false;
        $offset = null;
        for ($current = $counter; $current <= $counter + $window; ++$current) {
            if ($otp == $this->calculate($current)) {
                $valid = true;
                $offset = $current - $counter;
                break;
            }
        }
        $this->lastValidCounterOffset = $offset;

        return $valid;
    }

}

