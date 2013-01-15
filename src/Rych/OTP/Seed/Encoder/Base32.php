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
 * Base32 encoder/decoder class
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class Base32 implements EncoderInterface
{

    /**
     * @var string
     */
    private $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Test if an encoded string is compatible with this encoder/decoder
     *
     * @param string $data The encoded string.
     * @return boolean Returns true if the encoded string is compatible,
     *     otherwise false.
     */
    public function isValid($data)
    {
        return (preg_match("/[{$this->charset}]+=*/i", $data) === 1);
    }

    /**
     * Encode a string of raw data
     *
     * @param string $data String of raw data to encode.
     * @return string Returns the encoded string.
     */
    public function encode($data)
    {
        $encoded = '';

        if ($data) {
            $binString = '';
            // 'AB' => 01000001 01000010
            foreach (str_split($data) as $char) {
                $binString .= str_pad(decbin(ord($char)), 8, 0, STR_PAD_LEFT);
            }

            // 01000001 01000010 => 01000 00101 00001 00000 => 'IFBA'
            for ($offset = 0; $offset < strlen($binString); $offset += 5) {
                $chunk = str_pad(substr($binString, $offset, 5), 5, 0, STR_PAD_RIGHT);
                $encoded .= $this->charset[bindec($chunk)];
            }

            // 'IFBA' => 'IFBA===='
            if (strlen($encoded) % 8) {
                $encoded .= str_repeat('=', 8 - (strlen($encoded) % 8));
            }
        }

        return $encoded;
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
            // 'ifba====' => 'IFBA'
            $data = rtrim(strtoupper($data), '=');

            if (!$this->isValid($data)) {
                throw new \InvalidArgumentException('Invalid base32 string');
            }

            $binString = '';
            // 'IFBA' => 01000 00101 00001 00000
            foreach (str_split($data) as $char) {
                $binString .= str_pad(decbin(strpos($this->charset, $char)), 5, 0, STR_PAD_LEFT);
            }

            // 01000 00101 00001 00000 => 01000001 01000010
            // Assuming it's safe to drop the trailing bits, as if this is a
            // valid Base32 string, they'll be padding zeros anyway.
            $binString = substr($binString, 0, (floor(strlen($binString) / 8) * 8));

            // 01000001 01000010 => 'AB'
            for ($offset = 0; $offset < strlen($binString); $offset += 8) {
                $chunk = str_pad(substr($binString, $offset, 8), 8, 0, STR_PAD_RIGHT);
                $decoded .= chr(bindec($chunk));
            }
        }

        return $decoded;
    }

}
