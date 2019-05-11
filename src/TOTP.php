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
     * @inheritdoc OTPInterface::__construct()
     */
    public function __construct(string $secret, array $options = [])
    {
        $this->options["interval"] = 30;
        parent::__construct($secret, $options);
    }

    /**
     * @inheritdoc OTPInterface::calculate()
     */
    public function calculate(int $timestamp = null) : string
    {
        $counter = $this->timestampToCounter($timestamp ?? time());

        return parent::calculate($counter);
    }

    /**
     * @inheritdoc OTPInterface::verify()
     */
    public function verify(string $otp, int $timestamp = null) : bool
    {
        $counter = $this->timestampToCounter($timestamp ?? time());

        return parent::verify($otp, $counter);
    }

    /**
     * Convert a timestamp into a usable counter value
     *
     * @param   integer $timestamp  A UNIX timestamp.
     * @return  integer Returns the calculated counter value.
     */
    protected function timestampToCounter(int $timestamp) : int
    {
        $timestamp = (int) abs($timestamp);

        return (int) (($timestamp * 1000) / ($this->options["interval"] * 1000));
    }
}
