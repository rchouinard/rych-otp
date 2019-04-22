<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

namespace Rych\OTP\Encoder;

class RawEncoder implements EncoderInterface
{
    public function encode(string $data) : string
    {
        return $data;
    }

    public function decode(string $data) : string
    {
        return $data;
    }
}
