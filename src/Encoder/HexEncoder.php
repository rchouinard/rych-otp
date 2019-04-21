<?php

namespace Rych\OTP\Encoder;

class HexEncoder implements EncoderInterface
{
    public function encode(string $data) : string
    {
        return bin2hex($data);
    }

    public function decode(string $data) : string
    {
        return hex2bin($data);
    }
}
