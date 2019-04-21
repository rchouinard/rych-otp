<?php

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
