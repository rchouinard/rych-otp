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
abstract class AbstractOTP implements OTPInterface
{
    /** @var Seed */
    protected $secret;

    /**
     * @inheritdoc
     */
    public function __construct($secret, array $options = [])
    {
        $this->setSecret($secret);
        $this->processOptions($options);
    }

    /**
     * @param   array   $options
     * @return  void
     */
    abstract protected function processOptions(array $options) : void;

    /**
     * Get the shared secret
     *
     * @return  Seed    Returns an encoded {@link Seed} instance.
     */
    public function getSecret() : Seed
    {
        return $this->secret;
    }

    /**
     * Set the shared secret
     *
     * @param   Seed|string $secret
     * @return  self        Returns an instance of self for method chaining.
     */
    public function setSecret($secret) : self
    {
        if (!$secret instanceof Seed) {
            $secret = new Seed($secret);
        }
        $this->secret = $secret;

        return $this;
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
