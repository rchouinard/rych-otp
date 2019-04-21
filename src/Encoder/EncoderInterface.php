<?php

namespace Rych\OTP\Encoder;

interface EncoderInterface
{
    public function encode(string $data) : string;
    public function decode(string $data) : string;
}
