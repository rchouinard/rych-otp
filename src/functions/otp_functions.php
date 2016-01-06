<?php
/**
 * This file is part of Rych\OATH-OTP
 *
 * (c) Ryan Chouinard <rchouinard@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rych\Otp;

use Rych\Otp\Utility;

const TOTP_SHA1   = "sha1";
const TOTP_SHA256 = "sha256";
const TOTP_SHA512 = "sha512";

/**
 * Generate an HMAC-based one-time password
 *
 * hotp_generate() produces an HMAC-based one-time password as described in
 * {@link https://tools.ietf.org/html/rfc4226 RFC 4226}.
 *
 * @param   string  $secret     The shared secret string.
 * @param   integer $counter    A counter indicating the OTP to generate.
 * @param   integer $digits     Number of digits in the OTP.
 * @return  string              The generated OTP or FALSE on error.
 */
function hotp_generate($secret, $counter, $digits = 6)
{
    $hash = hash_hmac("sha1", Utility\counter_to_bin($counter), $secret, true);
    $otp = Utility\truncate_hash($hash);
    $otp %= pow(10, $digits);

    return str_pad($otp, $digits, 0, STR_PAD_LEFT);
}

/**
 * Validate an HMAC-based one-time password
 *
 * Validates a given HMAC-based one-time password against the given parameters.
 *
 * @param   string  $secret     The shared secret string.
 * @param   integer $counter    A counter indicating the OTP to generate.
 * @param   string  $otp        The OTP to validate.
 * @param   integer $window     How many OTPs after start counter to test.
 * @return  integer             Returns the counter offset value starting from
 *                              zero or FALSE on failure.
 */
function hotp_validate($secret, $counter, $otp, $window = 0)
{
    $offset = false;
    for ($current = $counter; $current <= $counter + $window; ++$current) {
        $test = hotp_generate($secret, $current, strlen($otp));

        if (Utility\secure_compare($test, $otp)) {
            $offset = $current - $counter;
            break;
        }
    }

    return $offset;
}

/**
 * Generate a time-based one-time password
 *
 * totp_generate() produces an time-based one-time password as described in
 * {@link https://tools.ietf.org/html/rfc6238 RFC 6238}.
 *
 * @param   string  $secret     The shared secret string.
 * @param   integer $now        A UNIX timestamp indicating the OTP to generate.
 * @param   integer $digits     Number of digits in the OTP.
 * @param   string  $algo       The hash algorithm.
 * @param   integer $step       Time step parameter.
 * @return  string              The generated OTP or FALSE on error.
 */
function totp_generate($secret, $now, $digits = 6, $algo = TOTP_SHA1, $step = 30)
{
    $counter = Utility\timestamp_to_counter($now, $step);
    $hash = hash_hmac($algo, Utility\counter_to_bin($counter), $secret, true);
    $otp = Utility\truncate_hash($hash);
    $otp %= pow(10, $digits);

    return str_pad($otp, $digits, 0, STR_PAD_LEFT);
}

/**
 * Validate a time-based one-time password
 *
 * Validates a given time-based one-time password against the given parameters.
 *
 * @param   string  $secret     The shared secret string.
 * @param   integer $now        A UNIX timestamp indicating the OTP to generate.
 * @param   string  $otp        The OTP to validate.
 * @param   integer $window     How many OTPs after start counter to test.
 * @param   string  $algo       The hash algorithm.
 * @param   integer $step       Time step parameter.
 * @return  integer             Returns the counter offset value starting from
 *                              zero or FALSE on failure.
 */
function totp_validate($secret, $now, $otp, $window = 0, $algo = TOTP_SHA1, $step = 30)
{
    $offset = false;
    for ($current = $now; $current <= $now + ($window * $step); $current += $step) {
        $test = totp_generate($secret, $current, strlen($otp), $algo, $step);

        if (Utility\secure_compare($test, $otp)) {
            $offset = Utility\timestamp_to_counter($current, $step) - Utility\timestamp_to_counter($now, $step);
            break;
        }
    }

    return $offset;
}

/**
 * Generate a random secret
 *
 * @codeCoverageIgnore
 * @param   string  $algo       The hash algorithm.
 * @return  string              Returns a raw secret suitable for use with the
 *                              chosen algorithm.
 */
function generate_secret($algo = TOTP_SHA1)
{
    return random_bytes(strlen(hash($algo, "", true)));
}
