<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

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
    public function __construct($secret, array $options = [])
    {
        $options = array_merge([
            "window" => 0,
            "timestep" => 30,
        ], array_change_key_case($options, CASE_LOWER));

        $this->setTimeStep($options["timestep"]);
        parent::__construct($secret, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function calculate(int $counter = null) : string
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
    public function setTimeStep(int $timeStep) : self
    {
        $timeStep = abs(intval($timeStep));
        $this->timeStep = $timeStep;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @param  integer $driftOffset Offset used to account for potential hardware RTC drift.
     */
    public function validate(string $otp, int $counter = null, int $driftOffset = 0) : bool
    {
        $counter = $this->timestampToCounter(($counter ?? time()), $this->getTimeStep());

        foreach ($this->getPossibleWindow() as $current) {
            if ($otp === parent::calculate($counter + $current + $driftOffset)) {
                $this->lastCounterOffset = $current + $driftOffset;

                return true;
            }
        }

        $this->lastCounterOffset = null;

        return false;
    }

    /**
     * @return array Returns an array of possible window values.
     */
    private function getPossibleWindow() : array
    {
        $possible = [0];
        $window = ceil($this->getWindow() / 2);

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
    private function timestampToCounter(int $timestamp, int $timeStep) : int
    {
        $timestamp = abs(intval($timestamp));
        $counter = intval(($timestamp * 1000) / ($timeStep * 1000));

        return $counter;
    }
}
