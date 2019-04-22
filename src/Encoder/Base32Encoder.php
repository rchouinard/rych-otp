<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

namespace Rych\OTP\Encoder;

use Rych\OTP\Encoder\Exception\RuntimeException;

class Base32Encoder implements EncoderInterface
{
    const CHARSET = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=";

    public function encode(string $data) : string
    {
        $encoded = "";

        if ($data != "") {
            $binData = "";

            array_map(function ($char) use (&$binData) {
                $binData .= str_pad(decbin(ord($char)), 8, "0", STR_PAD_LEFT);
            }, str_split($data, 1));

            array_map(function ($chunk) use (&$encoded) {
                $chunk = str_pad($chunk, 5, "0", STR_PAD_RIGHT);
                $encoded .= self::CHARSET[bindec($chunk)];
            }, str_split($binData, 5));

            if ($mod = strlen($encoded) % 8) {
                $encoded .= str_repeat(self::CHARSET[32], 8 - $mod);
            }
        }

        return $encoded;
    }

    /**
     * @throws RuntimeException
     */
    public function decode(string $data) : string
    {
        if (preg_match("/[^" . self::CHARSET . "]/", $data) > 0 || (strlen($data) % 8) != 0) {
            throw new RuntimeException(sprintf("Provided data is not valid Base32: %s", $data));
        }

        $decoded = "";

        if (($data = rtrim($data, self::CHARSET[32])) != "") {
            $binData = "";

            array_map(function ($char) use (&$binData) {
                $binData .= str_pad(decbin(strpos(self::CHARSET, $char)), 5, "0", STR_PAD_LEFT);
            }, str_split($data, 1));

            $binData = substr($binData, 0, intval(floor(strlen($binData) / 8) * 8));

            array_map(function ($chunk) use (&$decoded) {
                $chunk = str_pad($chunk, 8, "0", STR_PAD_RIGHT);
                $decoded .= chr(bindec($chunk));
            }, str_split($binData, 8));
        }

        return $decoded;
    }
}
