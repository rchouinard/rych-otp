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
class TOTP extends HOTP implements OTPInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(string $secret, array $options = [])
    {
        $this->options["interval"] = 30;
        parent::__construct($secret, $options);
    }

    /**
     * @inheritdoc
     */
    public function calculate(int $counter = null) : string
    {
        if ($counter === null) {
            $counter = time();
        }

        $counter = self::timestampToCounter($counter, $this->options["interval"]);
        $otp = parent::calculate($counter);

        return $otp;
    }

    /**
     * @inheritdoc
     * @param   integer $driftOffset    Offset used to account for potential hardware RTC drift.
     */
    public function verify(string $otp, int $counter = null, int $driftOffset = 0) : bool
    {
        $counter = $this->timestampToCounter($counter ?? time(), $this->options["interval"]);

        foreach ($this->getPossibleWindow() as $current) {
            if ($otp === parent::calculate($counter + $current + $driftOffset)) {
                $this->lastOffset = $current + $driftOffset;

                return true;
            }
        }

        $this->lastOffset = null;

        return false;
    }

    /**
     * @return  array   Returns an array of possible window values.
     */
    private function getPossibleWindow() : array
    {
        $possible = [0];
        $window = ceil($this->options["window"] / 2);

        if ($window > 0) {
            $possible = array_merge($possible, range(-$window, -1), range(1, $window));
        }

        return $possible;
    }

    /**
     * Convert a timestamp into a usable counter value
     *
     * @param   integer $timestamp  A UNIX timestamp.
     * @param   integer $timeStep   The timestep value.
     * @return  integer Returns the calculated counter value.
     */
    private function timestampToCounter(int $timestamp, int $timeStep) : int
    {
        $timestamp = (int) abs($timestamp);
        $counter = (int) (($timestamp * 1000) / ($timeStep * 1000));

        return $counter;
    }
}
