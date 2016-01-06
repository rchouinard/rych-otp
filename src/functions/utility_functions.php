<?php
/**
 * This file is part of Rych\OATH-OTP
 *
 * (c) Ryan Chouinard <rchouinard@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rych\Otp\Utility;

/**
 * @param   string  $known
 * @param   string  $user
 * @param   boolean $useBuiltin
 * @return  boolean
 */
function secure_compare($known, $user, $useBuiltin = true)
{
    if ($useBuiltin && function_exists("\hash_equals")) {
        return \hash_equals($known, $user);
    }

    if (strlen($known) !== $len = strlen($user)) {
        return false;
    }

    $check = 0;
    for ($i = 0; $i < $len; $i++) {
        $check |= (ord($known[$i]) ^ ord($user[$i]));
    }

    return ($check === 0);
}

/**
 * @param   integer $timestamp
 * @param   integer $step
 * @return  integer
 */
function timestamp_to_counter($timestamp, $step)
{
    return intval(($timestamp * 1000) / ($step * 1000));
}

/**
 * @param   string  $hash
 * @return  string
 */
function truncate_hash($hash)
{
    $offset = ord($hash[(strlen($hash) - 1)]) & 0xf;
    $otp  = (ord($hash[$offset + 0]) & 0x7f) << 24;
    $otp |= (ord($hash[$offset + 1]) & 0xff) << 16;
    $otp |= (ord($hash[$offset + 2]) & 0xff) << 8;
    $otp |= (ord($hash[$offset + 3]) & 0xff);

    return $otp;
}

/**
 * @param   integer $counter
 * @return  string
 */
function counter_to_bin($counter)
{
    $temp = "";
    while ($counter != 0) {
        $temp .= chr($counter & 0xff);
        $counter >>= 8;
    }

    return substr(str_pad(strrev($temp), 8, "\0", STR_PAD_LEFT), 0, 8);
}
