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
 * One-Time Password Interface
 */
interface OTPInterface
{

    /**
     * Class constructor
     *
     * @param Seed|string $secret  The shared secret key.
     * @param array       $options An array of options to be used when
     *                             generating one-time passwords.
     * @return void
     */
    public function __construct($secret, array $options);

    /**
     * Calculate a one-time password from a given counter value
     *
     * @param  integer $counter The counter value.
     * @return string  Returns the generated one-time password.
     */
    public function calculate($counter);

    /**
     * Validate a one-time password against a given counter value
     *
     * @param  string  $otp     The one-time password value.
     * @param  integer $counter The counter value.
     * @return boolean Returns TRUE if the one-time password is valid with the
     *                 given counter, or FALSE otherwise.
     */
    public function validate($otp, $counter);

}
