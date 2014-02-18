<?php
/**
 * Ryan's OATH-OTP Library
 *
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2014, Ryan Chouinard
 * @link https://github.com/rchouinard/rych-otp
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP;

use Rych\Random\Random;
use Rych\Random\Encoder\EncoderInterface;
use Rych\Random\Encoder\Base32Encoder;
use Rych\Random\Encoder\HexEncoder;
use Rych\Random\Encoder\RawEncoder;

/**
 * One-Time Password Seed/Key Class
 */
class Seed
{

    const FORMAT_BASE32 = 'base32';
    const FORMAT_HEX = 'hex';
    const FORMAT_RAW = 'raw';

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var string
     */
    private $value;

    /**
     * Class constructor
     *
     * @param  string  $value Optional; the seed value. If provided, the format
     *                        will be auto-detected.
     * @return void
     */
    public function __construct($value = null)
    {
        if ($value !== null) {
            $this->setValue($value);
        }

        $this->setFormat(self::FORMAT_HEX);
    }

    /**
     * Get the output format
     *
     * @return string  Returns the current output format.
     */
    public function getFormat()
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
     * Set the output format
     *
     * @param  string  $format The new output format.
     * @return self    Returns an instance of self for method chaining.
     */
    public function setFormat($format)
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
     * Get the seed value, optionally specifying an output format
     *
     * @param  string  $format Optional; output format. If not provided, value
     *                         is returned in the default format.
     * @return string  Returns the seed value in the requested format.
     */
    public function getValue($format = null)
    {
        return $this->encode($this->value, $format);
    }

    /**
     * Set the seed value, optionally specifying an input format
     *
     * @param  string  $value  The seed value.
     * @param  string  $format Optional; input format. If not provided, format
     *                         will be auto-detected.
     * @return self    Returns an instance of self for method chaining.
     */
    public function setValue($value, $format = null)
    {
        $this->value = $this->decode($value, $format);
    }

    /**
     * Get a string representation of the seed value
     *
     * @return string  Returns the seed value in the default format.
     */
    public function __toString()
    {
        $value = $this->value;
        if ($this->encoder instanceof EncoderInterface) {
            $value = $this->encoder->encode($value);
        }

        return (string) $value;
    }

    /**
     * Generate a new {@link Seed} instance with a new random value
     *
     * @param  integer $bytes  Optional; number of bytes in seed value.
     *                         Default of 20 produces a 160-bit seed value as
     *                         recommended by RFC 4226 Section 4 R6.
     * @param  Random  $random Optional; pre-configured instance of
     *                         the random generator class.
     * @return Seed    Returns an instance of Seed with a random value.
     */
    public static function generate($bytes = 20, Random $random = null)
    {
        if (!$random) {
            $random = new Random();
        }
        $output = $random->getRandomBytes((int) $bytes);

        return new Seed($output);
    }

    /**
     * Attempt to decode a seed value
     *
     * @param  string  $seed   The encoded seed value.
     * @param  string  $format Optional; value encoding format. If not
     *                         provided, format will be auto-detected.
     * @return string  Returns the decoded seed value.
     */
    private function decode($seed, $format = null)
    {
        $encoder = new RawEncoder();

        // Auto-detect
        if ($format === null) {
            if (preg_match('/^[0-9a-f]+$/i', $seed)) {
                $encoder = new HexEncoder();
            } elseif (preg_match('/^[2-7a-z]+$/i', $seed)) {
                $encoder = new Base32Encoder();
            }
        // User-specified
        } else {
            if ($format == self::FORMAT_HEX) {
                $encoder = new HexEncoder();
            } elseif ($format == self::FORMAT_BASE32) {
                $encoder = new Base32Encoder();
            }
        }

        $output = $encoder->decode($seed);

        return $output;
    }

    /**
     * Attempt to encode a seed value
     *
     * @param  string  $seed   The seed value.
     * @param  string  $format Optional; target encode format. If not provided,
     *                         default format is assumed.
     * @return string  Returns the encoded seed value.
     */
    private function encode($seed, $format = null)
    {
        $encoder = $this->encoder;

        if ($format == self::FORMAT_HEX) {
            $encoder = new HexEncoder();
        } elseif ($format == self::FORMAT_BASE32) {
            $encoder = new Base32Encoder();
        } elseif ($format == self::FORMAT_RAW) {
            $encoder = new RawEncoder();
        }

        $output = $encoder->encode($seed);

        return $output;
    }

}
