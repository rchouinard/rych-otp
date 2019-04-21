<?php

namespace Rych\OTP\Encoder;

use Rych\OTP\Encoder\Exception\RuntimeException;

class HexEncoder implements EncoderInterface
{
    public function encode(string $data) : string
    {
        return bin2hex($data);
    }

    /**
     * @throws RuntimeException
     */
    public function decode(string $data) : string
    {
        if (preg_match("/[^ABCDEF0123456789]/", strtoupper($data)) > 0 || (strlen($data) % 2) != 0) {
            throw new RuntimeException(sprintf("Provided data is not valid Hex: %s", $data));
        }

        return hex2bin($data);
    }
}
