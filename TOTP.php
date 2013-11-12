<?php
/**
 * Ryan's OATH-OTP Library
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2013, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP;

/**
 * RFC-6238 Time-Based One-Time Passwords
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2013, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TOTP extends HOTP
{

    /**
     * @var integer
     */
    protected $timeStep;

    /**
     * Class constructor
     *
     * @param string|\Rych\OTP\Seed $secret The shared secret key as a string
     *     or an instance of {@link \Rych\OTP\Seed}.
     * @param  array $options An array of configuration options.
     * @return void
     */
    public function __construct($secret, array $options = array ())
    {
        $options = array_merge(
            array (
                'window' => 0,
                'timestep' => 30,
            ),
            array_change_key_case($options, CASE_LOWER)
        );

        $this->setTimeStep($options['timestep']);
        parent::__construct($secret, $options);
    }

    /**
     * Generate a one-time password from a given counter value
     *
     * @param  integer $counter The counter value. Defaults to current
     *     timestamp.
     * @return string  Returns the generated one-time password.
     */
    public function calculate($counter = null)
    {
        if ($counter === null) {
            $counter = time();
        }

        $counter = $this->timestampToCounter($counter);
        $otp = parent::calculate($counter);

        return $otp;
    }

    /**
     * Get the timestep value
     *
     * @return integer The timestep value.
     */
    public function getTimeStep()
    {
        return $this->timeStep;
    }

    /**
     * Set the timestep value
     *
     * @param  integer        $timeStep The timestep value.
     * @return \Rych\OTP\TOTP Returns an instance of self for method chaining.
     */
    public function setTimeStep($timeStep)
    {
        $timeStep = abs(intval($timeStep));
        $this->timeStep = $timeStep;

        return $this;
    }

    /**
     * Validate an OTP
     *
     * @param  string  $otp     The OTP value.
     * @param  integer $counter The counter value. Defaults to current
     *     timestamp.
     * @return boolean Returns true if the supplied counter value is valid
     *     within the configured counter window, false otherwise.
     */
    public function validate($otp, $counter = null)
    {
        if ($counter === null) {
            $counter = time();
        }
        $window = $this->getWindow();
        $counter = $this->timestampToCounter($counter);

        $valid = false;
        $offset = null;
        $counterLow = max(0, $counter - intval(floor($window / 2)));
        $counterHigh = max(0, $counter + intval(ceil($window / 2)));
        for ($current = $counterLow; $current <= $counterHigh; ++$current) {
            if ($otp == parent::calculate($current)) {
                $valid = true;
                $offset = $current - $counter;
                break;
            }
        }
        $this->lastValidCounterOffset = $offset;

        return $valid;
    }

    /**
     * Convert a timestamp into a usable counter value
     *
     * @param  integer $timestamp A UNIX timestamp.
     * @return integer The calculated counter value.
     */
    private function timestampToCounter($timestamp)
    {
        $timeStep = $this->getTimeStep();
        $timestamp = abs(intval($timestamp));
        $counter = intval(($timestamp * 1000) / ($timeStep * 1000));

        return $counter;
    }

}

