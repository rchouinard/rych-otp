<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

namespace Rych\OTP;

/**
 * One-Time Password Base Class
 */
abstract class AbstractOTP
{
    /** @var int */
    protected $lastOffset;

    /** @var array */
    protected $options = [
        "digits" => 6,
        "hash_func" => "sha1",
        "window" => "2",
    ];

    /** @var string */
    protected $secret;

    /**
     * @inheritdoc
     */
    public function __construct(string $secret, array $options = [])
    {
        $this->secret = $secret;
        $this->options = $this->processOptions($options);
    }

    /**
     * @deprecated Use verify() instead.
     */
    public function validate(string $otp, int $counter) : bool
    {
        trigger_error("The validate() method has been deprecated. Please use verify() instead.", E_USER_DEPRECATED);

        return $this->verify();
    }

    /**
     * @param   array   $options
     * @return  array
     */
    protected function processOptions(array $options) : array
    {
        return array_merge($this->options, array_change_key_case($options, CASE_LOWER));
    }

    /**
     * Extract 4 bytes from a hash value
     *
     * Uses the method defined in RFC 4226, § 5.4.
     *
     * @param   string  $hash   Hash value.
     * @return  integer Returns the truncated hash value.
     */
    protected function truncateHash(string $hash) : int
    {
        $offset = ord($hash[19]) & 0xf;
        $value  = (ord($hash[$offset + 0]) & 0x7f) << 24;
        $value |= (ord($hash[$offset + 1]) & 0xff) << 16;
        $value |= (ord($hash[$offset + 2]) & 0xff) << 8;
        $value |= (ord($hash[$offset + 3]) & 0xff);

        return $value;
    }

    /**
     * Convert an integer counter into a string of 8 bytes
     *
     * @param   integer $counter    The counter value.
     * @return  string  Returns an 8-byte binary string.
     */
    protected function counterToString(int $counter) : string
    {
        $temp = "";
        while ($counter !== 0) {
            $temp .= chr($counter & 0xff);
            $counter >>= 8;
        }

        return substr(str_pad(strrev($temp), 8, "\0", STR_PAD_LEFT), 0, 8);
    }
}
