<?php

namespace Rych\OTP\Seed\Encoder;

use Rych\OTP\Seed\EncoderInterface;

class Hex implements EncoderInterface
{

    /**
     * Test if an encoded string is compatible with this encoder/decoder
     *
     * @param string $data The encoded string.
     * @return boolean Returns true if the encoded string is compatible,
     *     otherwise false.
     */
    public function isValid($data)
    {
        return (preg_match("/[0-9A-F]+/i", $data) === 1);
    }

    /**
     * Encode a string of raw data
     *
     * @param string $data String of raw data to encode.
     * @return string Returns the encoded string.
     */
    public function encode($data)
    {
        return bin2hex($data);
    }

    /**
     * Decode an encoded string
     *
     * @param string $data String of encoded data to decode.
     * @return string Returns the decoded string.
     * @throws \InvalidArgumentException Throws \InvalidArgumentException if
     *     given string is not valid for this encoder/decoder.
     */
    public function decode($data)
    {
        $decoded = '';

        if ($data) {
            if (!$this->isValid($data)) {
                throw new \InvalidArgumentException('Invalid hex string');
            }

            $decoded = hex2bin($data);
        }

        return $decoded;
    }

}
