<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

namespace Rych\OTP\Encoder;

interface EncoderInterface
{
    public function encode(string $data) : string;
    public function decode(string $data) : string;
}
