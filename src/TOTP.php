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
 * RFC-6238 Time-Based One-Time Password Class
 */
class TOTP extends HOTP
{

    /**
     * @var integer
     */
    protected $timeStep;

    /**
     * {@inheritdoc}
     */
    public function __construct($secret, array $options = array ())
    {
        $options = array_merge(array (
                'window' => 0,
                'timestep' => 30,
            ), array_change_key_case($options, CASE_LOWER)
        );

        $this->setTimeStep($options['timestep']);
        parent::__construct($secret, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($counter = null)
    {
        if ($counter === null) {
            $counter = time();
        }

        $counter = self::timestampToCounter($counter, $this->getTimeStep());
        $otp = parent::calculate($counter);

        return $otp;
    }

    /**
     * Get the timestep value
     *
     * @return integer Returns the timestep value.
     */
    public function getTimeStep()
    {
        return $this->timeStep;
    }

    /**
     * Set the timestep value
     *
     * @param  integer $timeStep The timestep value.
     * @return self    Returns an instance of self for method chaining.
     */
    public function setTimeStep($timeStep)
    {
        $timeStep = abs(intval($timeStep));
        $this->timeStep = $timeStep;

        return $this;
    }


    /**
     * @param string $otp the one time password value
     * @param int    $time     a unix timestamp
     * @param int    $offset   the value of lastCounterOffset from last time we had a valid password
     * @return bool true if the $password is valid
     */
    public function validate($otp, $time = null, $offset = 0)
    {
        if ($time === null) {
            $time = time();
        }
        $counter = $this->timestampToCounter($time, $this->getTimeStep());

        foreach ($this->getPossibleWindow() as $current) {
            if ($otp === parent::calculate($counter + $current + $offset)) {
                $this->lastCounterOffset = $current + $offset;
                return true;
            }
        }
        $this->lastCounterOffset = $offset;
        return false;
    }


    /**
     * @return array of potential offsets from the $current counter value to try
     */
    protected function getPossibleWindow()
    {
        $possible = [ 0 ]; // most likely value is tried first
        $window   = ceil($this->getWindow() / 2);
        if ($window > 0) {
            $possible = array_merge($possible, range(-$window, -1), range(1, $window));
        }
        return $possible;
    }


    /**
     * Convert a timestamp into a usable counter value
     *
     * @param  integer $timestamp A UNIX timestamp.
     * @param  integer $timeStep  The timestep value.
     * @return integer Returns the calculated counter value.
     */
    private static function timestampToCounter($timestamp, $timeStep)
    {
        $timestamp = abs(intval($timestamp));
        $counter = intval(($timestamp * 1000) / ($timeStep * 1000));

        return $counter;
    }

}
