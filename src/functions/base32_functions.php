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

const BASE32_CHARSET = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=";

/**
 * Encode data with base32
 *
 * Base32-encoded data takes about 60% more space than the original data.
 *
 * @param   string  $data       The data to encode.
 * @return  string              The encoded data as a string.
 */
function base32_encode($data)
{
    if (strlen($data) > 0) {
        $bin = array_reduce(str_split($data), function ($bin, $chr) {
            return $bin . str_pad(decbin(ord($chr)), 8, 0, STR_PAD_LEFT);
        }, "");

        $encoded = array_reduce(str_split($bin, 5), function ($encoded, $chunk) {
            $idx = bindec(str_pad($chunk, 5, 0, STR_PAD_RIGHT));
            return $encoded . BASE32_CHARSET[$idx];
        }, "");

        if ((strlen($encoded) % 8) !== 0) {
            $encoded .= str_repeat(BASE32_CHARSET[32], 8 - (strlen($encoded) % 8));
        }
    } else {
        $encoded = "";
    }

    return $encoded;
}

/**
 * Decode data encoded with base32
 *
 * @param   string  $data       The encoded data.
 * @param   boolean $strict     Returns FALSE if input contains character from
 *                              outside the base64 alphabet.
 * @return  string              Returns the original data or FALSE on failure.
 *                              The returned data may be binary.
 */
function base32_decode($data, $strict = false)
{
    $count = 0;
    $encoded = preg_replace("/[^".BASE32_CHARSET."]/", "", rtrim(strtoupper($data), BASE32_CHARSET[32]), -1, $count);

    if ($strict == true && $count > 0) {
        return false;
    }

    if (strlen($encoded) > 0) {
        $bin = array_reduce(str_split($encoded), function ($bin, $chr) {
            return $bin . str_pad(decbin(strpos(BASE32_CHARSET, $chr)), 5, 0, STR_PAD_LEFT);
        }, "");

        $binTrimmed = substr($bin, 0, (floor(strlen($bin) / 8) * 8));
        $decoded = array_reduce(str_split($binTrimmed, 8), function ($decoded, $chunk) {
            $idx = bindec(str_pad($chunk, 8, 0, STR_PAD_RIGHT));
            return $decoded . chr($idx);
        }, "");
    } else {
        $decoded = "";
    }

    return $decoded;
}
