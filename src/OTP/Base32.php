<?php
/**
 * RFC 4226 OTP Library
 *
 * @package OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace OTP;

/**
 * Base32 encoder/decoder class
 *
 * @package OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class Base32
{

    /**
     * @var string
     */
    private $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Decode a base32 string into its original data
     *
     * @param string $data Encoded string.
     * @return string Original data string.
     * @throws \InvalidArgumentException Thrown when the input data is not
     *     a valid base2 string.
     */
    public function decode($data)
    {
        $decoded = '';

        if ($data) {
            // 'ifba====' => 'IFBA'
            $data = rtrim(strtoupper($data), '=');

            if (!preg_match('/^[A-Z2-7]+$/', $data)) {
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
                $decoded .= trim(chr(bindec($chunk)));
            }
        }

        return $decoded;
    }

    /**
     * Encode data into a base32 string
     *
     * @param string $data Original data string.
     * @return string Encoded string.
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

}
