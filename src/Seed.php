<?php

/**
 * Ryan's OATH-OTP Library
 *
 * @copyright Ryan Chouinard <rchouinard@gmail.com>
 * @link https://github.com/rchouinard/rych-otp
 */

declare(strict_types=1);

namespace Rych\OTP;

use Rych\OTP\Encoder\{Base32Encoder, EncoderInterface, HexEncoder, RawEncoder};

/**
 * One-Time Password Secret/Key Class
 */
class Seed
{
    /** @var string Marker constant for base32 format
     */
    public const FORMAT_BASE32 = "base32";

    /** @var string Marker constant for hex format */
    public const FORMAT_HEX = "hex";

    /** @var string Marker constant for raw format */
    public const FORMAT_RAW = "raw";

    /** @var EncoderInterface Instance of secret value encoder/decoder class */
    private $encoder;

    /** @var string Secret value */
    private $value;

    /**
     * @param   string  $value  Secret value as a string.
     */
    public function __construct(string $value = null)
    {
        if ($value !== null) {
            $this->setValue($value);
        }

        $this->setFormat(self::FORMAT_HEX);
    }

    /**
     * Get the default encoding format
     *
     * @return  string  Returns the default encoding format.
     */
    public function getFormat() : string
    {
        switch (true) {
            case ($this->encoder instanceof Base32Encoder):
                $format = self::FORMAT_BASE32;
                break;
            case ($this->encoder instanceof HexEncoder):
                $format = self::FORMAT_HEX;
                break;
            case ($this->encoder instanceof RawEncoder):
            default:
                $format = self::FORMAT_RAW;
        }

        return $format;
    }

    /**
     * Set the default encoding format
     *
     * @param   string  $format The new default encoding format.
     * @return  self    Returns an instance of self for method chaining.
     */
    public function setFormat(string $format) : self
    {
        switch ($format) {
            case self::FORMAT_BASE32:
                $this->encoder = new Base32Encoder();
                break;
            case self::FORMAT_HEX:
                $this->encoder = new HexEncoder();
                break;
            case self::FORMAT_RAW:
            default:
                $this->encoder = new RawEncoder();
        }

        return $this;
    }

    /**
     * Get the secret value
     *
     * @param   string  $format Encoding format to use.
     *                          If not provided, default of `hex` or value
     *                          previously provided to `setFormat()` will be
     *                          used.
     * @return  string  Returns the secret value.
     */
    public function getValue(string $format = null) : string
    {
        return $this->encode($this->value, $format);
    }

    /**
     * Set the secret value
     *
     * @param   string  $value  The secret value.
     * @param   string  $format Encoding format of supplied string.
     *                          If not provided, will be auto-detected.
     * @return  self    Returns an instance of self for method chaining.
     */
    public function setValue(string $value, string $format = null) : self
    {
        $this->value = $this->decode($value, $format);

        return $this;
    }

    /**
     * Cast this instance to a string
     *
     * @return  string  Returns the secret value in the default format.
     */
    public function __toString() : string
    {
        $value = $this->value;
        if ($this->encoder instanceof EncoderInterface) {
            $value = $this->encoder->encode($this->value);
        }

        return $value;
    }

    /**
     * Generate a new secure random secret value
     *
     * @param   integer $bytes  Number of bytes to generate.
     *                          Default of 20 produces a 160-bit value as
     *                          recommended by RFC 4226 Section 4 R6.
     * @return  self    Returns an instance of Seed with a random value.
     */
    public static function generate(int $bytes = 20) : self
    {
        return new Seed(random_bytes($bytes));
    }

    /**
     * Decode a string
     *
     * @param   string  $data   The string to decode.
     * @param   string  $format Encoding format of supplied string.
     *                          If not provided, will be auto-detected.
     * @return  string  Returns The decoded value.
     */
    private function decode(string $data, string $format = null) : string
    {
        $encoder = new RawEncoder();

        // Auto-detect
        if ($format === null) {
            if (preg_match("/^[0-9a-f]+$/i", $data)) {
                $encoder = new HexEncoder();
            } elseif (preg_match("/^[2-7a-z]+$/i", $data)) {
                $encoder = new Base32Encoder();
            }
        // User-specified
        } elseif ($format === self::FORMAT_HEX) {
            $encoder = new HexEncoder();
        } elseif ($format === self::FORMAT_BASE32) {
            $encoder = new Base32Encoder();
        }

        return $encoder->decode($data);
    }

    /**
     * Encode a string
     *
     * @param   string  $data   The string to encode.
     * @param   string  $format Encoding format to use.
     *                          If not provided, default of `hex` or value
     *                          previously provided to `setFormat()` will be
     *                          used.
     * @return  string  Returns the encoded value.
     */
    private function encode(string $data, string $format = null) : string
    {
        $encoder = $this->encoder;

        if ($format === self::FORMAT_HEX) {
            $encoder = new HexEncoder();
        } elseif ($format === self::FORMAT_BASE32) {
            $encoder = new Base32Encoder();
        } elseif ($format === self::FORMAT_RAW) {
            $encoder = new RawEncoder();
        }

        return $encoder->encode($data);
    }
}
