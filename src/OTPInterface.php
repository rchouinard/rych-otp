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
 * One-Time Password Interface
 */
interface OTPInterface
{
    /**
     * @param   string  $secret     The shared secret key.
     * @param   array   $options    An array of options to be used when generating one-time passwords.
     * @return  void
     */
    public function __construct(string $secret, array $options);

    /**
     * Calculate a one-time password from a given counter value
     *
     * @param   integer $counter    The counter value.
     * @return  string  Returns the generated one-time password.
     */
    public function calculate(int $counter) : string;

    /**
     * Validate a one-time password against a given counter value
     *
     * @param   string  $otp        The one-time password value.
     * @param   integer $counter    The counter value.
     * @return  boolean Returns TRUE if the one-time password is valid with the given counter, or FALSE otherwise.
     */
    public function validate(string $otp, int $counter) : bool;
}
