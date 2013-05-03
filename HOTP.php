<?php
/**
 * OATH-OTP Library
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2013, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP;

use Rych\OTP\OTP;

/**
 * RFC-4226 HMAC-Based One-Time Passwords
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2013, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class HOTP extends OTP
{

    /**
     * @var integer
     */
    protected $window;

    /**
     * @var integer
     */
    protected $lastValidCounterOffset;

    /**
     * Class constructor
     *
     * @param string|\Rych\OTP\Seed $secret The shared secret key as a string or
     *     an instance of {@link \Rych\OTP\Seed}.
     * @param array $options An array of configuration options.
     * @return void
     */
    public function __construct($secret, array $options = array ())
    {
        $options = array_merge(
            array (
                'window' => 4,
            ),
            array_change_key_case($options, CASE_LOWER)
        );

        $this->setWindow($options['window']);
        parent::__construct($secret, $options);
    }

    /**
     * Set the window value
     *
     * @param integer $window The window value
     * @return \Rych\OTP\HOTP Returns an instance of self for method chaining.
     */
    public function setWindow($window)
    {
        $window = abs(intval($window));
        $this->window = $window;

        return $this;
    }

    /**
     * Get the window value
     *
     * @return integer The window value.
     */
    public function getWindow()
    {
        return $this->window;
    }

    /**
     * Validate an OTP
     *
     * @param string $otp The OTP value.
     * @param integer $counter The counter value. Defaults to 0.
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

}
