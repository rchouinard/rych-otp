<?php

namespace Rych\OTP\Encoder;

class Base64Encoder implements EncoderInterface
{
    public function encode(string $data) : string
    {
        return base64_encode($data);
    }

    public function decode(string $data) : string
    {
        return base64_decode($data);
    }
}
