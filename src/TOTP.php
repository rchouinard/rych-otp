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
     * {@inheritdoc}
     */
    public function validate($otp, $counter = null)
    {
        if ($counter === null) {
            $counter = time();
        }
        $window = $this->getWindow();
        $counter = self::timestampToCounter($counter, $this->getTimeStep());

        $valid = false;
        $offset = null;
        $counterLow = max(0, $counter - intval(floor($window / 2)));
        $counterHigh = max(0, $counter + intval(ceil($window / 2)));
        for ($current = $counterLow; $current <= $counterHigh; ++$current) {
            if ($otp === parent::calculate($current)) {
                $valid = true;
                $offset = $current - $counter;
                break;
            }
        }
        $this->lastCounterOffset = $offset;

        return $valid;
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
