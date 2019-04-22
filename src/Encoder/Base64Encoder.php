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

class Base64Encoder implements EncoderInterface
{
    public function encode(string $data) : string
    {
        return base64_encode($data);
    }

    /**
     * @throws RuntimeException
     */
    public function decode(string $data) : string
    {
        $charset  = "ABCDEFGHIJKLMNOPQRSTUVWZYZ";
        $charset .= "abcdefghijklmnopqrstuvqxyz";
        $charset .= "0123456789";

        if (preg_match("/[^${charset}\+\/=]/", $data) > 0 || (strlen($data) % 4) != 0) {
            throw new RuntimeException(sprintf("Provided data is not valid Base64: %s", $data));
        }

        return base64_decode($data);
    }
}
