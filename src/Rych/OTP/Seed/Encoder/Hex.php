<?php
/**
 * RFC 4226 OTP Library
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP\Seed\Encoder;

use Rych\OTP\Seed\EncoderInterface;

/**
 * Hex encoder/decoder class
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class Hex implements EncoderInterface
{

    /**
     * Test if an encoded string is compatible with this encoder/decoder
     *
     * @param string $data The encoded string.
     * @return boolean Returns true if the encoded string is compatible,
     *     otherwise false.
     */
    public static function isValid($data)
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
